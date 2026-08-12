<?php

namespace App\Console\Commands;

use App\Models\FundPdfExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneFundPdfExportsCommand extends Command
{
    protected $signature = 'funds:prune-pdf-exports {--days=7 : Delete exports older than this many days}';

    protected $description = 'Delete old fund PDF export records and their stored files';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $deleted = 0;

        FundPdfExport::where('created_at', '<', $cutoff)
            ->chunkById(100, function ($exports) use (&$deleted) {
                foreach ($exports as $export) {
                    if ($export->disk && $export->path) {
                        Storage::disk($export->disk)->delete($export->path);
                    }
                    $export->delete();
                    $deleted++;
                }
            });

        $this->info("Pruned {$deleted} fund PDF export(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
