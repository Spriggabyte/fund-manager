<?php

namespace App\Services\FundImport;

use App\Models\Fund;

class PriceGraphImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'PRICE_GRAPH');
    }

    public function label(): string
    {
        return 'price graph';
    }

    public function import(Fund $fund, string $filePath): void
    {
        $sheet = $this->loadDataSetSheet($filePath);

        $headers = [];
        $headerRow = $sheet->getRowIterator(1, 1)->current();
        foreach ($headerRow->getCellIterator() as $cell) {
            $headers[] = $cell->getValue();
        }

        // Column layout: A=Start Date, B=Description, C=Fund, D=Benchmark, E=ECPI/Inflation,
        // F/G=optional extras (bonds, peer)
        $portfolioData = [];
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
                $entry['benchmark'] = round((float) $cells[3], 2);
            }
            if (isset($cells[4]) && is_numeric($cells[4])) {
                $entry['inflation'] = round((float) $cells[4], 2);
            }
            if (isset($cells[5]) && is_numeric($cells[5])) {
                $entry['bonds'] = round((float) $cells[5], 2);
            }
            if (isset($cells[6]) && is_numeric($cells[6])) {
                $entry['peer'] = round((float) $cells[6], 2);
            }

            $portfolioData[] = $entry;
        }

        $chartData = $fund->chart_data ?? [];
        $chartData['portfolioData'] = $portfolioData;
        $chartData['headers'] = $headers;

        // International price graphs carry US CPI / MSCI / WGBI comparison
        // series (identified by the export's own column headers) rather than
        // the SA benchmark layout. Re-emit them under semantic keys for the
        // international templates' four-series performance chart.
        $fundCol = $cpiCol = $msciCol = $wgbiCol = null;
        foreach ($headers as $col => $header) {
            $header = (string) $header;
            if ($col === 2 && $header !== '') {
                $fundCol = $col;
            } elseif (str_contains($header, 'CPI')) {
                $cpiCol = $col;
            } elseif (str_contains($header, 'MSCI')) {
                $msciCol = $col;
            } elseif (str_contains($header, 'WGBI')) {
                $wgbiCol = $col;
            }
        }

        if ($fundCol !== null && $cpiCol !== null && $msciCol !== null && $wgbiCol !== null) {
            $semanticByColumn = [
                $fundCol => 'fund',
                $cpiCol => 'usInflation',
                $msciCol => 'worldEquities',
                $wgbiCol => 'worldBonds',
            ];
            $genericByColumn = [2 => 'fund', 3 => 'benchmark', 4 => 'inflation', 5 => 'bonds', 6 => 'peer'];
            $performanceData = [];
            foreach ($portfolioData as $entry) {
                $row = ['date' => $entry['date']];
                foreach ($semanticByColumn as $col => $semanticKey) {
                    $generic = $genericByColumn[$col] ?? null;
                    if ($generic !== null && isset($entry[$generic])) {
                        $row[$semanticKey] = $entry[$generic];
                    }
                }
                $performanceData[] = $row;
            }
            $chartData['performanceData'] = $performanceData;
        }

        $fund->chart_data = $chartData;
    }
}
