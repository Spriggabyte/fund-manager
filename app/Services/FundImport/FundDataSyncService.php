<?php

namespace App\Services\FundImport;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Mirrors the monthly Foord xlsx exports from the SFTP feed to local storage.
 *
 * Remote layout: {YYYY-MM}/{fund_code}/*.xlsx (relative to the sftp disk root).
 * Local layout:  fund-data/{YYYY-MM}/{fund_code}/*.xlsx on the local disk.
 *
 * Idempotent: a file is only downloaded when missing locally or when the
 * remote size differs (handles replaced/late-arriving exports on re-runs).
 */
class FundDataSyncService
{
    public const LOCAL_ROOT = 'fund-data';

    /**
     * Downloaded months that contain data for a fund code, newest first,
     * as month => xlsx file count. Drives the edit page's import card.
     *
     * The count is class-aware so the card advertises what would actually be
     * imported, not every class's exports sitting in the same folder.
     *
     * @return array<string, int>
     */
    public function availableMonths(?string $fundCode, ?string $classCode = null): array
    {
        if (! $fundCode) {
            return [];
        }

        $local = Storage::disk('local');
        $manager = new FundImportManager;

        return collect($local->directories(self::LOCAL_ROOT))
            ->map(fn (string $dir): string => basename($dir))
            ->filter(fn (string $month): bool => (bool) preg_match('/^\d{4}-\d{2}$/', $month))
            ->sortDesc()
            ->mapWithKeys(function (string $month) use ($local, $manager, $fundCode, $classCode): array {
                $xlsx = preg_grep('/\.xlsx$/i', $local->files(self::LOCAL_ROOT."/{$month}/{$fundCode}")) ?: [];

                return [$month => count($manager->filesForClass(array_values($xlsx), $fundCode, $classCode))];
            })
            ->filter()
            ->all();
    }

    /**
     * @return array{months: string[], downloaded: string[], skipped: string[], errors: array<string, string>}
     */
    public function sync(?string $onlyMonth = null, bool $dryRun = false): array
    {
        $remote = Storage::disk('sftp');
        $local = Storage::disk('local');

        $report = ['months' => [], 'downloaded' => [], 'skipped' => [], 'errors' => []];

        $months = collect($remote->directories(''))
            ->map(fn (string $dir): string => basename($dir))
            ->filter(fn (string $dir): bool => (bool) preg_match('/^\d{4}-\d{2}$/', $dir))
            ->when($onlyMonth !== null, fn ($months) => $months->filter(fn (string $dir): bool => $dir === $onlyMonth))
            ->sort()
            ->values();

        foreach ($months as $month) {
            $report['months'][] = $month;

            foreach ($remote->directories($month) as $fundDir) {
                foreach ($remote->files($fundDir) as $remotePath) {
                    $target = self::LOCAL_ROOT."/{$month}/".basename($fundDir).'/'.basename($remotePath);

                    try {
                        if ($local->exists($target) && $local->size($target) === $remote->size($remotePath)) {
                            $report['skipped'][] = $target;

                            continue;
                        }

                        if (! $dryRun) {
                            $stream = $remote->readStream($remotePath);
                            $local->writeStream($target, $stream);
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }

                        $report['downloaded'][] = $target;
                    } catch (Throwable $e) {
                        $report['errors'][$remotePath] = $e->getMessage();
                    }
                }
            }
        }

        return $report;
    }
}
