<?php

namespace App\Services\FundImport;

use App\Models\Fund;

class AlsiGraphImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'ALSI_GRAPH');
    }

    public function label(): string
    {
        return 'ALSI graph';
    }

    /**
     * Import the equity fund's monthly relative-return-vs-ALSI graph data.
     *
     * Source layout (Foord ALSI Graph export, e.g. FS.811:GRAPH_1):
     *   A: Month End Date (Excel serial)
     *   B: Graph Name
     *   C/D: "Relative Return (Alsi -ve)" and its value
     *   E/F: "Relative Return (Alsi +ve)" and its value
     * Exactly one of the two values is nonzero per month; the +ve value can
     * itself be negative (fund lagged a rising market). Columns W/X repeat
     * the values as decimals and are outside the pair range.
     */
    public function import(Fund $fund, string $filePath): void
    {
        $sheet = $this->loadDataSetSheet($filePath);

        $monthlyData = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }

            $serial = $cells[0] ?? null;
            if (! is_numeric($serial)) {
                continue;
            }

            $negative = null;
            $positive = null;
            for ($i = 2; $i <= 20; $i += 2) {
                $label = $cells[$i] ?? null;
                $value = $cells[$i + 1] ?? null;
                if (! $label || ! is_numeric($value)) {
                    continue;
                }
                if (str_contains($label, '-ve)')) {
                    $negative = (float) $value;
                } elseif (str_contains($label, '+ve)')) {
                    $positive = (float) $value;
                }
            }

            if ($negative === null && $positive === null) {
                continue;
            }

            $benchmarkNegative = $negative !== null && abs($negative) > 1e-9;

            $monthlyData[] = [
                'date' => $this->excelSerialToMonth($serial),
                'relative' => round($benchmarkNegative ? $negative : ($positive ?? 0.0), 2),
                'benchmarkNegative' => $benchmarkNegative,
            ];
        }

        $chartData = $fund->chart_data ?? [];
        $chartData['monthlyData'] = $monthlyData;
        $fund->chart_data = $chartData;
    }
}
