<?php

namespace App\Services\FundImport;

use App\Models\Fund;
use Carbon\Carbon;

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

        // The global equity (Lux) exports carry three series — fund, the
        // MSCI benchmark and the Morningstar peer-group average. The peer
        // column is named "Fund Benchmark (2nd)" on 877/879, "Fund Misc
        // (3rd)" on 878 and "Fund Benchmark (4th)" on 880; 821's column E is
        // "Misc (1st)" and must not match.
        // Identified by its own headers (an MSCI benchmark in column D plus
        // the peer column in E, no CPI/WGBI columns); re-emitted under
        // semantic keys for the three-series PORTFOLIO PERFORMANCE VS
        // BENCHMARK chart.
        if (! isset($chartData['performanceData']) && $fundCol !== null && $msciCol === 3) {
            $peerHeader = (string) ($headers[4] ?? '');
            $isPeerColumn = str_contains($peerHeader, 'Benchmark (2nd)')
                || str_contains($peerHeader, 'Misc (3rd)')
                || str_contains($peerHeader, 'Benchmark (4th)');
            if ($isPeerColumn && $cpiCol === null && $wgbiCol === null) {
                $performanceData = [];
                foreach ($portfolioData as $entry) {
                    $row = ['date' => $entry['date']];
                    if (isset($entry['fund'])) {
                        $row['fund'] = $entry['fund'];
                    }
                    if (isset($entry['benchmark'])) {
                        $row['benchmark'] = $entry['benchmark'];
                    }
                    // Column E parses under the generic 'inflation' key
                    if (isset($entry['inflation'])) {
                        $row['peerGroup'] = $entry['inflation'];
                    }
                    $performanceData[] = $row;
                }
                $chartData['performanceData'] = $performanceData;
            }
        }

        // The Prescient global equity feeder export (823) carries only two
        // series — the class price and its MSCI ACWI benchmark — feeding the
        // two-line ILLUSTRATIVE PERFORMANCE chart. Identified by an MSCI
        // benchmark in column D with NOTHING in column E: 821/878/879/880
        // also lead with an MSCI benchmark but carry a peer or second
        // benchmark alongside it, and must not fall into this branch.
        if (! isset($chartData['performanceData'])
            && $fundCol !== null
            && $msciCol === 3
            && $cpiCol === null
            && $wgbiCol === null
            && trim((string) ($headers[4] ?? '')) === '') {
            $performanceData = [];
            foreach ($portfolioData as $entry) {
                $row = ['date' => $entry['date']];
                if (isset($entry['fund'])) {
                    $row['fund'] = $entry['fund'];
                }
                if (isset($entry['benchmark'])) {
                    $row['benchmark'] = $entry['benchmark'];
                }
                $performanceData[] = $row;
            }
            $chartData['performanceData'] = $performanceData;
        }

        $fund->chart_data = $chartData;

        // The Australian feeder quotes two inception columns that the
        // factsheet export cannot fill on its own — see below.
        if (($fund->template ?? '') === Fund::AUSTRALIAN_FEEDER_TEMPLATE
            && ! empty($chartData['performanceData'])) {
            $this->fillFeederInceptionColumns($fund, $chartData['performanceData']);
        }
    }

    /**
     * The 880 PORTFOLIO PERFORMANCE table quotes two inception returns per
     * row: SINCE INCEPTION runs from the master fund's launch (2 April 2013,
     * where the index series starts) and SINCE 11 AUG 22 from the feeder
     * class's own launch.
     *
     * The factsheet export carries only one of each: FOORD_I_TO_D is the
     * fund's return since the class launched, while the comparators'
     * FOORD_COMP_n_I_TO_D run from the start of their series — the master
     * fund's launch. The two missing corners are annualised here off the
     * indexed price series, which is the same history the chart draws.
     *
     * Verified against the signed-off reference: the fund's 9.3 and the
     * benchmark's 16.8 both reproduce exactly.
     */
    private function fillFeederInceptionColumns(Fund $fund, array $performanceData): void
    {
        $table = $fund->performance_table ?? [];
        $rows = $table['rows'] ?? [];
        if (! $rows) {
            return;
        }

        $last = $performanceData[count($performanceData) - 1];

        // The series is indexed to 100 at the master fund's launch, one month
        // before the first row, so the full span is one month per row.
        $spanMonths = count($performanceData);

        // The class launched mid-month; the reference measures from the
        // month-end index before it (11 August 2022 → the July 2022 close).
        $classBaseMonths = null;
        $classBase = null;
        if ($fund->inception_date) {
            try {
                $baseKey = Carbon::parse($fund->inception_date)->subMonthNoOverflow()->format('Y-m');
            } catch (\Exception) {
                $baseKey = null;
            }
            foreach ($performanceData as $position => $entry) {
                if ($baseKey !== null && ($entry['date'] ?? null) === $baseKey) {
                    $classBase = $entry;
                    $classBaseMonths = count($performanceData) - 1 - $position;
                    break;
                }
            }
        }

        $annualise = function (?float $from, ?float $to, ?int $months): ?float {
            if (! $from || ! $to || ! $months || $from <= 0 || $to <= 0) {
                return null;
            }

            return round(((($to / $from) ** (12 / $months)) - 1) * 100, 1);
        };

        // Row name (as the factsheet importer writes it) → its series.
        $seriesByRow = ['fund' => 'fund', 'benchmark' => 'benchmark', 'comparator 2' => 'peerGroup'];

        foreach ($rows as $i => $row) {
            $series = $seriesByRow[strtolower(trim((string) ($row['name'] ?? '')))] ?? null;
            if ($series === null || ! isset($last[$series])) {
                continue;
            }

            // The fund's own since-launch figure comes from the feed (it is
            // measured from the exact launch date, not the month end).
            if ($series === 'fund') {
                $value = $annualise(100.0, (float) $last[$series], $spanMonths);
                if ($value !== null) {
                    $rows[$i]['sinceInception'] = $value;
                }

                continue;
            }

            $value = $annualise(
                isset($classBase[$series]) ? (float) $classBase[$series] : null,
                (float) $last[$series],
                $classBaseMonths
            );
            if ($value !== null) {
                $rows[$i]['sinceClassInception'] = $value;
            }
        }

        $table['rows'] = $rows;
        $fund->performance_table = $table;
    }
}
