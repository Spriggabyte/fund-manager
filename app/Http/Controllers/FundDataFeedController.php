<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Services\FundImport\FundDataSyncService;
use App\Services\FundImport\FundImportManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Bulk data-feed actions on the funds index: pull the newest month off the
 * SFTP feed, and import each fund's newest downloaded month in one go.
 */
class FundDataFeedController extends Controller
{
    /**
     * Mirror the newest month folder from the SFTP feed to local storage.
     * Idempotent — unchanged files are skipped (see FundDataSyncService).
     */
    public function download(FundDataSyncService $syncService): RedirectResponse
    {
        if (! config('filesystems.disks.sftp.host')) {
            return redirect()->route('funds.index')
                ->with('error', 'SFTP_HOST is not configured — nothing to download.');
        }

        set_time_limit(600);

        try {
            $month = $syncService->latestRemoteMonth();
            if (! $month) {
                return redirect()->route('funds.index')
                    ->with('error', 'No month folders (YYYY-MM) found on the SFTP server.');
            }

            $report = $syncService->sync($month);
        } catch (Throwable $e) {
            Log::error('Data feed download failed', ['exception' => $e]);

            return redirect()->route('funds.index')
                ->with('error', 'Could not reach the SFTP server: '.$e->getMessage());
        }

        $summary = sprintf(
            'Downloaded %d file(s) for %s; %d already up to date.',
            count($report['downloaded']),
            $month,
            count($report['skipped']),
        );

        if ($report['errors']) {
            return redirect()->route('funds.index')
                ->with('error', $summary.' '.count($report['errors']).' file(s) failed: '.implode(', ', array_keys($report['errors'])));
        }

        return redirect()->route('funds.index')->with('success', $summary);
    }

    /**
     * Import each fund's newest downloaded month. Funds already imported for
     * that month (a "Before data feed import (YYYY-MM)" revision exists) are
     * skipped so hand-set post-import values aren't clobbered on a re-click.
     */
    public function importLatest(FundDataSyncService $syncService, FundImportManager $manager): RedirectResponse
    {
        set_time_limit(600);

        $imported = [];
        $alreadyDone = [];
        $noData = [];
        $failed = [];

        $funds = Fund::whereNotNull('fund_code')->where('fund_code', '!=', '')->orderBy('name')->get();

        foreach ($funds as $fund) {
            $label = trim($fund->name.' '.($fund->class ?? ''));
            $month = array_key_first($syncService->availableMonths($fund->fund_code, $fund->class_code));

            if (! $month) {
                $noData[] = $label;

                continue;
            }

            $revisionSummary = "Before data feed import ({$month})";
            if ($fund->revisions()->where('change_summary', $revisionSummary)->exists()) {
                $alreadyDone[] = $label;

                continue;
            }

            try {
                $result = $manager->importDirectoryWithSnapshot(
                    $fund,
                    Storage::disk('local')->path(FundDataSyncService::LOCAL_ROOT."/{$month}/{$fund->fund_code}"),
                    $revisionSummary
                );

                if ($result['imported']) {
                    $imported[] = "{$label} ({$month})";
                } else {
                    $noData[] = $label;
                }
            } catch (Throwable $e) {
                Log::error('Bulk data feed import failed', ['fund_id' => $fund->id, 'exception' => $e]);
                $failed[] = "{$label}: {$e->getMessage()}";
            }
        }

        $parts = [count($imported).' fund(s) imported'.($imported ? ': '.implode(', ', $imported) : '').'.'];
        if ($alreadyDone) {
            $parts[] = count($alreadyDone).' already imported for their latest month (skipped).';
        }
        if ($noData) {
            $parts[] = 'No downloaded data: '.implode(', ', $noData).'.';
        }

        $redirect = redirect()->route('funds.index')->with('success', implode(' ', $parts));

        return $failed ? $redirect->with('error', 'Failed: '.implode('; ', $failed)) : $redirect;
    }
}
