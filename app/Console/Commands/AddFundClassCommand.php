<?php

namespace App\Console\Commands;

use App\Models\Fund;
use App\Services\FundImport\FundDataSyncService;
use App\Services\FundImport\FundImportManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AddFundClassCommand extends Command
{
    protected $signature = 'fund:add-class
        {source : Fund ID to clone the static content from}
        {class : Share class code for the new fund (A, B2, B3, R, ...)}
        {--name= : Override the derived fund name}
        {--import= : Import this downloaded month (YYYY-MM) straight after creating}';

    protected $description = 'Create a new share class of an existing fund, cloning its static content';

    public function handle(FundImportManager $manager): int
    {
        $source = Fund::find($this->argument('source'));
        if (! $source) {
            $this->error("Fund {$this->argument('source')} not found.");

            return self::FAILURE;
        }

        $classCode = strtoupper(trim($this->argument('class')));
        if (! preg_match('/^[A-Z][0-9]*$/', $classCode)) {
            $this->error("Invalid class code '{$classCode}' — expected a letter optionally followed by digits (A, B2, R1).");

            return self::FAILURE;
        }

        if (! $source->fund_code) {
            $this->error("Fund {$source->id} has no fund code, so the new class could not be matched to a data-feed folder.");

            return self::FAILURE;
        }

        if (Fund::where('fund_code', $source->fund_code)->where('class_code', $classCode)->exists()) {
            $this->error("Fund code {$source->fund_code} class {$classCode} already exists.");

            return self::FAILURE;
        }

        $fund = $source->replicate();
        $fund->class_code = $classCode;
        $fund->name = $this->option('name') ?: $this->deriveName($source->name, $classCode);

        // Everything below is per-class and must come from that class's own
        // export rather than be inherited from the fund we cloned.
        $fund->isin_number = null;
        $fund->unit_price = null;
        $fund->number_of_units = null;
        $fund->last_distributions = null;
        $fund->save();

        $this->info("Created fund {$fund->id} — {$fund->name} ({$source->fund_code} class {$classCode}), cloned from fund {$source->id}.");

        if (! $month = $this->option('import')) {
            $this->line("Import its data with:  php artisan fund:import {$fund->id} <directory>");

            return self::SUCCESS;
        }

        return $this->importMonth($fund, $month, $manager);
    }

    /**
     * Swap the trailing "CLASS x" token for the new class, e.g.
     * "FOORD BALANCED FUND — CLASS A" => "FOORD BALANCED FUND — CLASS B3".
     */
    private function deriveName(string $sourceName, string $classCode): string
    {
        $name = preg_replace('/(CLASS\s+)[A-Z][0-9]*$/iu', '${1}'.$classCode, $sourceName, 1, $count);

        return $count ? $name : $sourceName." — CLASS {$classCode}";
    }

    private function importMonth(Fund $fund, string $month, FundImportManager $manager): int
    {
        $directory = FundDataSyncService::LOCAL_ROOT."/{$month}/{$fund->fund_code}";
        $disk = Storage::disk('local');

        if (! $disk->exists($directory)) {
            $this->warn("No downloaded data for {$month} (fund code {$fund->fund_code}) — the fund was created but not imported.");

            return self::SUCCESS;
        }

        $result = $manager->importDirectoryWithSnapshot(
            $fund,
            $disk->path($directory),
            "Before data feed import ({$month})"
        );

        foreach ($result['imported'] as $file => $label) {
            $this->line("  ✓ {$file} → {$label}");
        }
        foreach ($result['skipped'] as $file) {
            $this->warn("  ! {$file} — no importer registered for this export type, skipped");
        }
        if ($result['otherClasses']) {
            $this->line('  Ignored (other share classes): '.implode(', ', $result['otherClasses']));
        }

        if (! $result['imported']) {
            $this->error("No recognised class {$fund->class_code} exports found in the {$month} download.");

            return self::FAILURE;
        }

        $this->info("Imported {$month} into fund {$fund->id}.");

        return self::SUCCESS;
    }
}
