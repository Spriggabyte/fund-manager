<?php

namespace App\Services\FundImport;

use App\Models\Fund;

class RollingReturnGraphImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'ROLLING_1_YEAR_GRAPH');
    }

    public function label(): string
    {
        return 'rolling one-year return graph';
    }

    /**
     * Import the rolling one-year return series (conservative fund's
     * STRATEGY — ROLLING ONE-YEAR RETURN bar chart).
     *
     * Source layout (Foord Performance Summary export, shared by every class):
     *   A: Start Date (Excel serial)
     *   B: Description — the period end, e.g. "Dec 2014 (1Y)"
     *   C: "<code> Fund Published" — the published fund series (decimal fractions)
     *   D+: per-class series (not published on the fact sheets)
     */
    public function import(Fund $fund, string $filePath): void
    {
        $sheet = $this->loadDataSetSheet($filePath);

        $headers = [];
        foreach ($sheet->getRowIterator(1, 1)->current()->getCellIterator() as $cell) {
            $headers[] = (string) $cell->getValue();
        }

        // The fact sheets plot the published fund series; fall back to
        // column C, its position in every export seen so far.
        $valueCol = 2;
        foreach ($headers as $col => $header) {
            if (str_contains(strtoupper($header), 'FUND PUBLISHED')) {
                $valueCol = $col;
                break;
            }
        }

        $rollingReturnData = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }

            $description = $cells[1] ?? null;
            $value = $cells[$valueCol] ?? null;
            if (! $description || ! is_numeric($value)) {
                continue;
            }

            $date = $this->parseMonthYear(trim(preg_replace('/\s*\(1Y\)\s*$/', '', $description)));

            $rollingReturnData[] = [
                'date' => $date ?? $description,
                'value' => round(((float) $value) * 100, 2),
            ];
        }

        $chartData = $fund->chart_data ?? [];
        $chartData['rollingReturnData'] = $rollingReturnData;
        $fund->chart_data = $chartData;
    }
}
