<?php

namespace App\Services\FundImport;

use App\Models\Fund;

/**
 * Imports the flexible fund's COST_REG28_GRAPH export: the fund's indexed
 * return series alongside the Regulation 28-compliant comparison portfolio
 * (Foord Balanced), rendered as the "Investment Strategy vs Reg 28
 * Portfolios" chart.
 */
class CostReg28GraphImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'COST_REG28_GRAPH');
    }

    public function label(): string
    {
        return 'Reg 28 comparison graph';
    }

    public function import(Fund $fund, string $filePath): void
    {
        $sheet = $this->loadDataSetSheet($filePath);

        // Column layout: A=Start Date, B=Description (e.g. "Mar 2008"),
        // C=Fund, D=Reg 28 comparison portfolio (810 Balanced).
        $strategyData = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }

            $description = $cells[1] ?? null;
            if (! $description) {
                continue;
            }

            $date = $this->parseMonthYear($description);
            $entry = ['date' => $date ?? $description];

            if (isset($cells[2]) && is_numeric($cells[2])) {
                $entry['fund'] = round((float) $cells[2], 2);
            }
            if (isset($cells[3]) && is_numeric($cells[3])) {
                $entry['comparison'] = round((float) $cells[3], 2);
            }

            $strategyData[] = $entry;
        }

        $chartData = $fund->chart_data ?? [];
        $chartData['strategyData'] = $strategyData;
        $fund->chart_data = $chartData;
    }
}
