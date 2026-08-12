<?php

namespace App\Services;

use App\Models\Fund;
use App\Services\FundImport\AlsiGraphImporter;
use App\Services\FundImport\FactsheetImporter;
use App\Services\FundImport\InflationGraphImporter;
use App\Services\FundImport\PriceGraphImporter;

/**
 * Thin facade over the per-file-type importers in App\Services\FundImport.
 * Kept for the existing controller call sites; new code (and the fund:import
 * command) should go through FundImportManager instead.
 */
class ExcelImportService
{
    public function importFactsheet(Fund $fund, string $filePath): void
    {
        (new FactsheetImporter)->import($fund, $filePath);
    }

    public function importPriceGraph(Fund $fund, string $filePath): void
    {
        (new PriceGraphImporter)->import($fund, $filePath);
    }

    public function importInflationGraph(Fund $fund, string $filePath): void
    {
        (new InflationGraphImporter)->import($fund, $filePath);
    }

    public function importAlsiGraph(Fund $fund, string $filePath): void
    {
        (new AlsiGraphImporter)->import($fund, $filePath);
    }
}
