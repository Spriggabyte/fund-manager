<?php

namespace App\Services;

use App\Models\Fund;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    public function importFactsheet(Fund $fund, string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Data Set') ?? $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }
            if (! empty($cells[0]) && isset($cells[1])) {
                $data[$cells[0]] = $cells[1];
            }
        }

        $this->mapScalarFields($fund, $data);
        $this->mapTopInvestments($fund, $data);
        $this->mapAssetAllocation($fund, $data);
        $this->mapEquitySectorAllocation($fund, $data);
        $this->mapGeographicExposure($fund, $data);
        $this->mapPerformanceTable($fund, $data);
        $this->mapTotalInvestmentCharge($fund, $data);
    }

    public function importPriceGraph(Fund $fund, string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Data Set') ?? $spreadsheet->getActiveSheet();

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
        $fund->chart_data = $chartData;
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
    public function importInflationGraph(Fund $fund, string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Data Set') ?? $spreadsheet->getActiveSheet();

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

    private function excelSerialToMonth(int|float|string $serial): string
    {
        // Excel serial 1 = 1900-01-01 (with the legacy 1900 leap-year bug).
        $unix = ((int) $serial - 25569) * 86400;

        return gmdate('Y-m', $unix);
    }

    private function mapScalarFields(Fund $fund, array $data): void
    {
        if (isset($data['MONTH_END_DATE'])) {
            $fund->fund_date = $data['MONTH_END_DATE'];
        }
        if (isset($data['PORTFOLIO_SIZE'])) {
            $fund->portfolio_size = $data['PORTFOLIO_SIZE'];
        }
        if (isset($data['UNIT_PRICE'])) {
            $fund->unit_price = $data['UNIT_PRICE'];
        }
        if (isset($data['NUMBER_OF_UNITS'])) {
            $fund->number_of_units = $data['NUMBER_OF_UNITS'];
        }
        if (isset($data['ISIN'])) {
            $fund->isin_number = $data['ISIN'];
        }
        if (isset($data['SEDOL']) && $data['SEDOL'] !== '0') {
            $fund->sedol = $data['SEDOL'];
        }
        if (isset($data['PUBLISHED_DATE'])) {
            $fund->important_info_published_date = 'Published on '.$data['PUBLISHED_DATE'].'.';
        }

        // Distributions
        if (isset($data['LAST_DISTRIBUTION_DATE']) && isset($data['LAST_DISTRIBUTION_AMOUNT'])) {
            $dist = $data['LAST_DISTRIBUTION_DATE'].': '.$data['LAST_DISTRIBUTION_AMOUNT'];
            if (isset($data['SECOND_LAST_DISTRIBUTION_DATE']) && isset($data['SECOND_LAST_DISTRIBUTION_AMOUNT'])) {
                $dist .= '<br>'.$data['SECOND_LAST_DISTRIBUTION_DATE'].': '.$data['SECOND_LAST_DISTRIBUTION_AMOUNT'];
            }
            $fund->last_distributions = $dist;
        }

        if (isset($data['TER_FOR_FUND_FINANCIAL_YEAR_END'])) {
            $this->updateTerFootnote($fund, $data);
        }
    }

    private function mapTopInvestments(Fund $fund, array $data): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $security = $data["TOPX_SECURITY_{$i}"] ?? null;
            if (! $security) {
                break;
            }
            $rows[] = [
                'security' => $security,
                'assetClass' => $data["TOPX_ASSET_CLASS_{$i}"] ?? '',
                'market' => $data["TOPX_MARKET_{$i}"] ?? '',
                'percentage' => $this->toNumber($data["TOPX_PERCENT_OF_FUNDS_{$i}"] ?? '0'),
            ];
        }

        if ($rows) {
            $topInvestments = $fund->top_investments ?? [];
            $topInvestments['rows'] = $rows;
            if (! isset($topInvestments['title'])) {
                $topInvestments['title'] = 'TOP 10 INVESTMENTS';
            }
            if (! isset($topInvestments['headers'])) {
                $topInvestments['headers'] = ['SECURITY', 'ASSET CLASS', 'MARKET', '% OF FUND'];
            }
            $fund->top_investments = $topInvestments;
        }
    }

    private function mapAssetAllocation(Fund $fund, array $data): void
    {
        // Detect format: SA balanced (AA_DOM_*) vs international (AAOT_*)
        $hasSaFormat = isset($data['AA_DOM_EQ']) || isset($data['AA_DOM_TOTAL']);
        $hasGlobalFormat = isset($data['AAOT_RANK_1_ITEM']);

        if ($hasSaFormat) {
            $this->mapSaAssetAllocation($fund, $data);
        } elseif ($hasGlobalFormat) {
            $this->mapGlobalAssetAllocation($fund, $data);
        }
    }

    private function mapSaAssetAllocation(Fund $fund, array $data): void
    {
        $categories = [
            'EQ' => 'Equities',
            'PROP' => 'Listed property',
            'DEBT' => 'Corporate bonds',
            'BOND' => 'Government bonds',
            'COMM' => 'Commodities',
            'CASH' => 'Money market',
        ];

        $rows = [];
        foreach ($categories as $key => $name) {
            $sa = $data["AA_DOM_{$key}"] ?? null;
            $foreign = $data["AA_FRGN_{$key}"] ?? null;
            $total = $data["AA_TOTAL_{$key}"] ?? null;

            if ($total === null) {
                continue;
            }

            $change = $data["AA_TOTAL_DIFF_{$key}"] ?? '0';
            $sign = $data["AA_TOTAL_DIFF_SIGN_{$key}"] ?? '+';

            $rows[] = [
                'name' => $name,
                'sa' => $this->toNumber($sa ?? 0),
                'foreign' => $this->toNumber($foreign ?? 0),
                'total' => $this->toNumber($total),
                'change' => ($sign === '-' ? '▼ ' : '▲ ').$change,
                'changeDirection' => $sign === '-' ? 'down' : 'up',
            ];
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['rows'] = $rows;
            $assetAllocation['title'] = $assetAllocation['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)';
            $assetAllocation['subtitle'] = $assetAllocation['subtitle'] ?? 'Change since '.$this->formatChangeDate($data);
            $assetAllocation['headers'] = ['', 'SA (100)', 'FOREIGN (45)', 'TOTAL', 'CHANGE'];
            $assetAllocation['total'] = [
                'name' => 'TOTAL',
                'sa' => $this->toNumber($data['AA_DOM_TOTAL'] ?? 0),
                'foreign' => $this->toNumber($data['AA_FRGN_TOTAL'] ?? 0),
                'total' => 100,
                'change' => '',
            ];
            $fund->asset_allocation = $assetAllocation;
        }
    }

    private function mapGlobalAssetAllocation(Fund $fund, array $data): void
    {
        $rows = [];
        for ($i = 1; $i <= 8; $i++) {
            $item = $data["AAOT_RANK_{$i}_ITEM"] ?? null;
            if (! $item) {
                continue;
            }

            $current = $data["AAOT_RANK_{$i}_CURRENT"] ?? '-';
            $change = $data["AAOT_RANK_{$i}_CHANGE"] ?? '0';
            $sign = $data["AAOT_RANK_{$i}_CHANGE_SIGN"] ?? '+';

            $rows[] = [
                'name' => $item,
                'total' => $current === '-' ? 0 : $this->toNumber($current),
                'change' => ($sign === '-' ? '▼ ' : '▲ ').$change,
                'changeDirection' => $sign === '-' ? 'down' : 'up',
            ];
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['rows'] = $rows;
            $assetAllocation['title'] = $assetAllocation['title'] ?? 'ASSET ALLOCATION %';
            $assetAllocation['headers'] = ['', 'TOTAL', 'CHANGE'];
            $fund->asset_allocation = $assetAllocation;
        }
    }

    private function mapEquitySectorAllocation(Fund $fund, array $data): void
    {
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $item = $data["ESAOT_RANK_{$i}_ITEM"] ?? null;
            if (! $item) {
                continue;
            }
            $current = $data["ESAOT_RANK_{$i}_CURRENT"] ?? '-';
            $change = $data["ESAOT_RANK_{$i}_CHANGE"] ?? '0';

            $rows[] = [
                'name' => $item,
                'percentage' => $current === '-' ? 0 : $this->toNumber($current),
                'change' => $this->toNumber($change),
            ];
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['equitySectors'] = $rows;
            $fund->asset_allocation = $assetAllocation;
        }
    }

    private function mapGeographicExposure(Fund $fund, array $data): void
    {
        $regions = ['AFRME', 'ASIA_EM', 'EUR', 'PAC', 'US', 'LATAM_EM'];
        $regionNames = [
            'AFRME' => 'Africa/Middle East',
            'ASIA_EM' => 'Asia (emerging)',
            'EUR' => 'Europe',
            'PAC' => 'Asia Pacific',
            'US' => 'United States',
            'LATAM_EM' => 'Latin America',
        ];

        $rows = [];
        foreach ($regions as $region) {
            $total = $data["GEO_EXP_{$region}_TOTAL"] ?? null;
            if ($total === null || $total === '-') {
                continue;
            }
            $rows[] = [
                'name' => $regionNames[$region] ?? $region,
                'equity' => $this->dashToZero($data["GEO_EXP_{$region}_EQTY"] ?? '-'),
                'cash' => $this->dashToZero($data["GEO_EXP_{$region}_CASH"] ?? '-'),
                'total' => $this->toNumber($total),
            ];
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['geographicExposure'] = $rows;
            $assetAllocation['geographicTotals'] = [
                'equity' => $this->dashToZero($data['GEO_EXP_EQTY_TOTAL'] ?? '-'),
                'cash' => $this->dashToZero($data['GEO_EXP_CASH_TOTAL'] ?? '-'),
            ];
            $fund->asset_allocation = $assetAllocation;
        }
    }

    private function mapPerformanceTable(Fund $fund, array $data): void
    {
        // All possible period mappings (both SA and international formats)
        $periods = [
            'M_TO_D' => 'thisMonth',
            'YTD' => 'ytd',
            'Y_TO_D' => '1yr',       // SA format for 1yr
            '1Y_TO_D' => '1yr',      // International format for 1yr
            'DY_TO_D' => 'distYield',
            '2Y_TO_D' => '2yrs',
            '3M_TO_D' => '3months',
            '6M_TO_D' => '6months',
            '3Y_TO_D' => '3yrs',
            '5Y_TO_D' => '5yrs',
            '7Y_TO_D' => '7yrs',
            '10Y_TO_D' => '10yrs',
            '15Y_TO_D' => '15yrs',
            '20Y_TO_D' => '20yrs',
            '25Y_TO_D' => '25yrs',
            'I_TO_D' => 'sinceInception',
        ];

        $fundRow = ['name' => 'Fund'];
        $benchmarkRow = ['name' => 'Benchmark'];
        $highestRow = ['name' => 'Fund highest'];
        $lowestRow = ['name' => 'Fund lowest'];

        foreach ($periods as $excelKey => $jsonKey) {
            $val = $data["FOORD_{$excelKey}"] ?? null;
            if ($val !== null && $val !== '') {
                $fundRow[$jsonKey] = $this->toNumber($val);
            }
        }

        if (isset($data['FOORD_CASH_VALUE'])) {
            $fundRow['cashValue'] = $data['FOORD_CASH_VALUE'];
        }

        // Highest/lowest rows
        $hlPeriods = [
            'Y1' => '1yr', 'Y2' => '2yrs', 'Y3' => '3yrs', 'Y5' => '5yrs',
            'Y7' => '7yrs', 'Y10' => '10yrs', 'Y15' => '15yrs',
            'Y20' => '20yrs', 'Y25' => '25yrs', 'INCEPTION' => 'sinceInception',
        ];
        foreach ($hlPeriods as $excelKey => $jsonKey) {
            $hVal = $data["FOORD_HIGHEST_{$excelKey}"] ?? null;
            $lVal = $data["FOORD_LOWEST_{$excelKey}"] ?? null;
            if ($hVal !== null && $hVal !== '') {
                $highestRow[$jsonKey] = $this->toNumber($hVal);
            }
            if ($lVal !== null && $lVal !== '') {
                $lowestRow[$jsonKey] = $this->toNumber($lVal);
            }
        }

        // Benchmark (COMP_1)
        foreach ($periods as $excelKey => $jsonKey) {
            $val = $data["FOORD_COMP_1_{$excelKey}"] ?? null;
            if ($val !== null && $val !== '') {
                $benchmarkRow[$jsonKey] = $this->toNumber($val);
            }
        }
        if (isset($data['FOORD_COMP_1_CASH_VALUE'])) {
            $benchmarkRow['cashValue'] = $data['FOORD_COMP_1_CASH_VALUE'];
        }
        // SA format uses FOORD_COMP_1_1Y_TO_D instead of FOORD_COMP_1_Y_TO_D
        if (isset($data['FOORD_COMP_1_1Y_TO_D'])) {
            $benchmarkRow['1yr'] = $this->toNumber($data['FOORD_COMP_1_1Y_TO_D']);
        }

        $rows = [$fundRow, $benchmarkRow, $highestRow, $lowestRow];

        // Additional comparators (COMP_2 through COMP_7)
        for ($c = 2; $c <= 7; $c++) {
            $hasData = false;
            $compRow = ['name' => "Comparator {$c}"];
            foreach ($periods as $excelKey => $jsonKey) {
                $val = $data["FOORD_COMP_{$c}_{$excelKey}"] ?? null;
                if ($val !== null && $val !== '' && $val !== '0.0') {
                    $compRow[$jsonKey] = $this->toNumber($val);
                    $hasData = true;
                }
            }
            if (isset($data["FOORD_COMP_{$c}_CASH_VALUE"])) {
                $compRow['cashValue'] = $data["FOORD_COMP_{$c}_CASH_VALUE"];
            }
            if ($hasData) {
                $rows[] = $compRow;
            }
        }

        $performanceTable = $fund->performance_table ?? [];
        $performanceTable['rows'] = $rows;
        if (! isset($performanceTable['title'])) {
            $performanceTable['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)';
        }
        $fund->performance_table = $performanceTable;
    }

    private function mapTotalInvestmentCharge(Fund $fund, array $data): void
    {
        $fees = $fund->fees ?? [];
        $tic = $fees['totalInvestmentCharge'] ?? [];

        // Detect format: SA (SA_TER_*) vs international (GLOBAL_TER_*)
        $hasSaFormat = isset($data['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH']);
        $hasGlobalFormat = isset($data['GLOBAL_TER_BASIC_12_MONTH']);

        $rows = [];

        if ($hasSaFormat) {
            $mapping = [
                'SA_TER_TOTAL_EXPENSE_RATIO' => 'Total expense ratio (TER)',
                'SA_TER_MANAGERS_CHARGE' => '— Manager\'s charge (basic)',
                'SA_TER_PERFORMANCE_CHARGE' => '— Performance charge',
                'SA_TER_FOORD_GLOBAL_CHARGE' => '— Foord global charges',
                'SA_TER_VAT_AND_SUNDRY_COSTS' => '— VAT and sundry costs',
                'SA_TER_TRANSACTIONS_COSTS_INCL_VAT' => 'Transaction costs (incl VAT)',
            ];
            foreach ($mapping as $prefix => $name) {
                $m12 = $data["{$prefix}_12_MONTH"] ?? null;
                $m36 = $data["{$prefix}_36_MONTH"] ?? null;
                if ($m12 !== null) {
                    $rows[] = [
                        'name' => $name,
                        '12m' => $this->toNumber($m12),
                        '36m' => $m36 !== null ? $this->toNumber($m36) : null,
                    ];
                }
            }
            $totalM12 = $data['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH'] ?? null;
            $totalM36 = $data['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH'] ?? null;
            if ($totalM12 !== null) {
                $tic['total'] = [
                    'name' => 'Total investment charge',
                    '12m' => $this->toNumber($totalM12),
                    '36m' => $totalM36 !== null ? $this->toNumber($totalM36) : null,
                ];
            }
        } elseif ($hasGlobalFormat) {
            $mapping = [
                'GLOBAL_TER_BASIC' => 'Basic fee',
                'GLOBAL_TER_PERFORMANCE' => 'Performance fee',
                'GLOBAL_TER_TRANSACTION_COSTS' => 'Transaction costs',
            ];
            foreach ($mapping as $prefix => $name) {
                $m12 = $data["{$prefix}_12_MONTH"] ?? null;
                $m36 = $data["{$prefix}_36_MONTH"] ?? null;
                if ($m12 !== null && $m12 !== 'ER9') {
                    $rows[] = [
                        'name' => $name,
                        '12m' => $this->toNumber($m12),
                        '36m' => $m36 !== null && $m36 !== 'ER9' ? $this->toNumber($m36) : null,
                    ];
                }
            }
            $totalM12 = $data['GLOBAL_TER_TOTAL_12_MONTH'] ?? null;
            $totalM36 = $data['GLOBAL_TER_TOTAL_36_MONTH'] ?? null;
            if ($totalM12 !== null && $totalM12 !== 'ER9') {
                $tic['total'] = [
                    'name' => 'Total investment charge',
                    '12m' => $this->toNumber($totalM12),
                    '36m' => $totalM36 !== null && $totalM36 !== 'ER9' ? $this->toNumber($totalM36) : null,
                ];
            }
        }

        if ($rows) {
            $tic['rows'] = $rows;
            $fees['totalInvestmentCharge'] = $tic;
            $fund->fees = $fees;
        }
    }

    private function updateTerFootnote(Fund $fund, array $data): void
    {
        $ter = $data['TER_FOR_FUND_FINANCIAL_YEAR_END'] ?? null;
        $yearEnd = $data['FUND_FINANCIAL_YEAR_END'] ?? null;

        if ($ter && $yearEnd) {
            $fees = $fund->fees ?? [];
            $tic = $fees['totalInvestmentCharge'] ?? [];
            $desc = $tic['description'] ?? '';

            if (preg_match('/The TER for the fund.*was \d+\.\d+%/', $desc)) {
                $desc = preg_replace(
                    '/The TER for the fund.*was \d+\.\d+%\.?/',
                    "The TER for the fund's financial year ended {$yearEnd} was {$ter}.",
                    $desc
                );
                $tic['description'] = $desc;
                $fees['totalInvestmentCharge'] = $tic;
                $fund->fees = $fees;
            }
        }
    }

    private function formatChangeDate(array $data): string
    {
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
        ];

        $lastQuarter = $data['LAST_QUARTER_END'] ?? $data['MONTH_END_LESS_THREE'] ?? null;

        return $lastQuarter ?? '';
    }

    private function toNumber(mixed $value): float|int
    {
        if (is_numeric($value)) {
            $num = $value + 0;

            return is_float($num) ? $num : (int) $num;
        }

        return 0;
    }

    private function dashToZero(string $value): float|int
    {
        return $value === '-' ? 0 : $this->toNumber($value);
    }

    private function parseMonthYear(string $description): ?string
    {
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
        ];

        if (preg_match('/^(\w{3})\s+(\d{4})$/', $description, $m)) {
            $month = $months[$m[1]] ?? null;
            if ($month) {
                return $m[2].'-'.$month;
            }
        }

        return null;
    }
}
