<?php

namespace App\Console\Commands;

use App\Services\FundImport\FundDataSyncService;
use Illuminate\Console\Command;

class SyncFundDataCommand extends Command
{
    protected $signature = 'funds:sync-data
        {--month= : Only sync one month folder (YYYY-MM)}
        {--dry-run : List what would be downloaded without writing}';

    protected $description = 'Mirror the monthly fund data exports from the SFTP feed to local storage';

    public function handle(FundDataSyncService $service): int
    {
        if (! config('filesystems.disks.sftp.host')) {
            $this->warn('SFTP_HOST is not configured — nothing to sync.');

            return self::SUCCESS;
        }

        $report = $service->sync($this->option('month'), (bool) $this->option('dry-run'));

        $verb = $this->option('dry-run') ? 'Would download' : 'Downloaded';
        foreach ($report['downloaded'] as $path) {
            $this->line("  ↓ {$path}");
        }
        foreach ($report['errors'] as $path => $message) {
            $this->error("  ✗ {$path}: {$message}");
        }

        $this->info(sprintf(
            'Months seen: %s | %s: %d | Skipped (up to date): %d | Errors: %d',
            $report['months'] ? implode(', ', $report['months']) : '(none)',
            $verb,
            count($report['downloaded']),
            count($report['skipped']),
            count($report['errors']),
        ));

        return $report['errors'] ? self::FAILURE : self::SUCCESS;
    }
}
