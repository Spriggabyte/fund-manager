<?php

namespace App\Services\FundImport;

use App\Models\Fund;

class InflationGraphImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'INFLATION_GRAPH');
    }

    public function label(): string
    {
        return 'inflation graph';
    }

    /**
     * Import the SA inflation rolling 5-year graph data.
     *
     * Source layout (Foord SA Inflation Graph export):
     *   A: Month End Date (Excel serial)
     *   B: Graph Name (e.g. W.GLB:GRAPH_1)
     *   C/E/G/I: field labels — typically "5 Year CPI", "5% Hurdle", "Excess Return", "5 Year Comp. Ret."
     *   D/F/H/J: numeric values for each label, already in percent units
     */
    public function import(Fund $fund, string $filePath): void
    {
        $sheet = $this->loadDataSetSheet($filePath);

        $labelToKey = [
            '5 Year CPI' => 'inflation',
            'Inflation' => 'inflation',
            'CPI' => 'inflation',
            '5% Hurdle' => 'hurdle',
            'Hurdle' => 'hurdle',
            'Excess Return' => 'excess',
            'Excess' => 'excess',
            '5 Year Comp. Ret.' => 'composite',
            'Composite' => 'composite',
            'Composite Return' => 'composite',
        ];

        $inflationData = [];
        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }

            $serial = $cells[0] ?? null;
            if (! is_numeric($serial)) {
                continue;
            }

            $entry = ['date' => $this->excelSerialToMonth($serial)];

            // Pairs: (C,D), (E,F), (G,H), (I,J), (K,L), (M,N), (O,P), (Q,R), (S,T), (U,V)
            for ($i = 2; $i <= 20; $i += 2) {
                $label = $cells[$i] ?? null;
                $value = $cells[$i + 1] ?? null;
                if (! $label || ! is_numeric($value)) {
                    continue;
                }
                $key = $labelToKey[trim($label)] ?? null;
                if ($key) {
                    $entry[$key] = round((float) $value, 2);
                }
            }

            // Skip rows without any data values
            if (count($entry) > 1) {
                $inflationData[] = $entry;
            }
        }

        $chartData = $fund->chart_data ?? [];
        $chartData['inflationData'] = $inflationData;
        $fund->chart_data = $chartData;
    }
}
