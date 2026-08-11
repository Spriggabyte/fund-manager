<?php

namespace App\Console\Commands;

use App\Models\Fund;
use App\Services\FundImport\FundImportManager;
use Illuminate\Console\Command;

class ImportFundDataCommand extends Command
{
    protected $signature = 'fund:import
        {fund : Fund ID}
        {directory : Folder containing the Foord xlsx exports}
        {--dry-run : Report what would be imported without saving}';

    protected $description = 'Import a fund\'s factsheet and graph Excel exports from a directory';

    public function handle(FundImportManager $manager): int
    {
        $fund = Fund::find($this->argument('fund'));
        if (! $fund) {
            $this->error("Fund {$this->argument('fund')} not found.");

            return self::FAILURE;
        }

        $directory = $this->argument('directory');
        if (! is_dir($directory)) {
            $this->error("Directory not found: {$directory}");

            return self::FAILURE;
        }

        $this->info("Importing into fund {$fund->id} ({$fund->name})");

        if ($fund->fund_code && $fund->class_code) {
            $this->line("  Selecting {$fund->fund_code} class {$fund->class_code} exports only.");
        } elseif ($fund->fund_code) {
            $this->warn("  Fund {$fund->id} has no class code — every file in the folder will be imported.");
        }

        if ($this->option('dry-run')) {
            $result = $manager->importDirectory($fund, $directory);
            $result['changed'] = array_keys($fund->getDirty());
        } else {
            $result = $manager->importDirectoryWithSnapshot($fund, $directory, 'Before Excel import (fund:import)');
        }

        if (! $result['imported']) {
            $this->error('No recognised export files found in '.$directory);

            return self::FAILURE;
        }

        foreach ($result['imported'] as $file => $label) {
            $this->line("  ✓ {$file} → {$label}");
        }
        foreach ($result['skipped'] as $file) {
            $this->warn("  ! {$file} — no importer registered for this export type, skipped");
        }
        if ($result['otherClasses']) {
            $this->line('  Ignored (other share classes): '.implode(', ', $result['otherClasses']));
        }

        $this->line('Changed fields: '.($result['changed'] ? implode(', ', $result['changed']) : '(none)'));

        $this->info($this->option('dry-run') ? 'Dry run — nothing saved.' : 'Saved.');

        return self::SUCCESS;
    }
}
