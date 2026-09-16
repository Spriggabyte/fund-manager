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

    /**
     * Foord only ever exports a combined COST_REG28_GRAPH file for the 817 A
     * class. Other classes (B2) need their "Investment Strategy vs Reg 28
     * Portfolios" chart built from data the feed does provide: the class's
     * own indexed fund series (already parsed by PriceGraphImporter into
     * chart_data['portfolioData']) alongside the matching class of the
     * comparator fund's (810 Foord Balanced) PRICE_GRAPH export, rebased to
     * 100 at the flexible fund's own first data point the same way Foord's
     * combined A-class export does it.
     *
     * Verified against the signed-off B2 reference (QC 2026-09-14): this
     * reproduces the reference sheet's R 700 / R 559 chart end-labels
     * exactly (699.55 / 558.87 rounded) — the fund 30 record had been left
     * with fund 13's stale strategyData ever since the class was cloned.
     */
    public function synthesizeForClass(Fund $fund, string $comparatorFile): void
    {
        $chartData = $fund->chart_data ?? [];
        $portfolioData = $chartData['portfolioData'] ?? [];
        if (! $portfolioData || ! is_file($comparatorFile)) {
            return;
        }

        $sheet = $this->loadDataSetSheet($comparatorFile);
        $comparator = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }

            $description = $cells[1] ?? null;
            if (! $description || ! isset($cells[2]) || ! is_numeric($cells[2])) {
                continue;
            }

            $date = $this->parseMonthYear($description) ?? $description;
            $comparator[$date] = (float) $cells[2];
        }

        $firstDate = $portfolioData[0]['date'] ?? null;
        $base = $firstDate !== null ? ($comparator[$firstDate] ?? null) : null;
        if (! $base) {
            return;
        }

        $strategyData = [];
        foreach ($portfolioData as $entry) {
            $date = $entry['date'] ?? null;
            if ($date === null || ! isset($entry['fund']) || ! isset($comparator[$date])) {
                continue;
            }

            $strategyData[] = [
                'date' => $date,
                'fund' => $entry['fund'],
                'comparison' => round($comparator[$date] / $base * 100, 2),
            ];
        }

        if ($strategyData) {
            $chartData['strategyData'] = $strategyData;
            $fund->chart_data = $chartData;
        }
    }
}
