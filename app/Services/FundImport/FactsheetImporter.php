<?php

namespace App\Services\FundImport;

use App\Models\Fund;
use Carbon\Carbon;

class FactsheetImporter extends AbstractExcelImporter
{
    public function supports(string $filename): bool
    {
        return $this->filenameMatches($filename, 'FACTSHEET');
    }

    public function label(): string
    {
        return 'factsheet';
    }

    public function import(Fund $fund, string $filePath): void
    {
        $data = $this->readKeyValuePairs($this->loadDataSetSheet($filePath));

        $this->mapScalarFields($fund, $data);
        $this->mapTopInvestments($fund, $data);
        $this->mapAssetAllocation($fund, $data);
        $this->mapEquitySectorAllocation($fund, $data);
        $this->mapGeographicExposure($fund, $data);
        $this->mapPerformanceTable($fund, $data);
        $this->mapTotalInvestmentCharge($fund, $data);
        $this->updateChartDescription($fund, $data);
    }

    private function mapScalarFields(Fund $fund, array $data): void
    {
        if (isset($data['MONTH_END_DATE'])) {
            $fund->fund_date = $data['MONTH_END_DATE'];
        }
        if (isset($data['PORTFOLIO_SIZE'])) {
            // Sheets export the bare figure ("5.0 billion"); the fact sheets
            // display the fund currency — rands for SA funds, dollars for the
            // USD-based international fund (TOTAL FUND SIZE "$1.5 billion").
            $size = $data['PORTFOLIO_SIZE'];
            $currency = ($fund->template ?? '') === 'show-international' ? '$' : 'R';
            $fund->portfolio_size = preg_match('/^\s*[R$€£]/u', $size) ? $size : $currency.$size;
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
            // The signed-off designs zero-pad the day ("Published on 04 February 2026.").
            $published = preg_replace_callback(
                '/^(\d{1,2}) /',
                fn ($m) => str_pad($m[1], 2, '0', STR_PAD_LEFT).' ',
                $data['PUBLISHED_DATE']
            );
            $fund->important_info_published_date = 'Published on '.$published.'.';
        }

        // Distributions. The equity design omits the colon between date and
        // amount; the other signed-off designs include it.
        if (isset($data['LAST_DISTRIBUTION_DATE']) && isset($data['LAST_DISTRIBUTION_AMOUNT'])) {
            $separator = $fund->template === 'show-equity' ? ' ' : ': ';
            $dist = $data['LAST_DISTRIBUTION_DATE'].$separator.$data['LAST_DISTRIBUTION_AMOUNT'];
            if (isset($data['SECOND_LAST_DISTRIBUTION_DATE']) && isset($data['SECOND_LAST_DISTRIBUTION_AMOUNT'])) {
                $dist .= '<br>'.$data['SECOND_LAST_DISTRIBUTION_DATE'].$separator.$data['SECOND_LAST_DISTRIBUTION_AMOUNT'];
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
        // Detect format: equity (AA_SHARE_*) vs SA balanced (AA_DOM_*) vs
        // international (AAOT_*)
        if (isset($data['AA_SHARE_CURRENT'])) {
            $this->mapEquityAssetAllocation($fund, $data);
        } elseif (isset($data['AA_DOM_EQ']) || isset($data['AA_DOM_TOTAL'])) {
            $this->mapSaAssetAllocation($fund, $data);
        } elseif (isset($data['AAOT_RANK_1_ITEM'])) {
            $this->mapGlobalAssetAllocation($fund, $data);
        }
    }

    /**
     * Equity fund layout: current/prior month columns, JSE subsectors
     * indented beneath the equity total.
     */
    private function mapEquityAssetAllocation(Fund $fund, array $data): void
    {
        $categories = [
            'AA_SHARE' => ['name' => 'JSE equity securities', 'indent' => false],
            'AA_RES' => ['name' => '— Resources', 'indent' => true],
            'AA_FIN' => ['name' => '— Financials', 'indent' => true],
            'AA_IND' => ['name' => '— Industrials', 'indent' => true],
            'AA_PROPERTY' => ['name' => 'JSE property', 'indent' => false],
            'AA_COMMOD' => ['name' => 'Commodities', 'indent' => false],
            'AA_CASH' => ['name' => 'Money market', 'indent' => false],
        ];

        $rows = [];
        foreach ($categories as $key => $meta) {
            $current = $data["{$key}_CURRENT"] ?? null;
            if ($current === null) {
                continue;
            }
            $rows[] = [
                'name' => $meta['name'],
                'current' => (string) $current,
                'previous' => (string) ($data["{$key}_PRIOR"] ?? '-'),
                'indent' => $meta['indent'],
                'isTotal' => false,
            ];
        }

        if (! $rows) {
            return;
        }

        // The reference prints TOTAL as 100 and covers rounding drift with
        // the "Totals may not cast perfectly" note.
        $rows[] = ['name' => 'TOTAL', 'current' => '100', 'previous' => '100', 'indent' => false, 'isTotal' => true];

        $assetAllocation = $fund->asset_allocation ?? [];
        $assetAllocation['rows'] = $rows;
        $assetAllocation['title'] = $assetAllocation['title'] ?? 'ASSET ALLOCATION %';

        $monthEnd = $this->parseMonthEnd($data);
        if ($monthEnd) {
            $assetAllocation['headers'] = [
                '',
                strtoupper($monthEnd->format('j M Y')),
                strtoupper($monthEnd->copy()->subMonthNoOverflow()->endOfMonth()->format('j M Y')),
            ];
        }

        $fund->asset_allocation = $assetAllocation;
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

        // Regulation 28 / mandate max limits shown in brackets after each asset
        // class on the published fact sheet ("ASSET ALLOCATION % (MAX LIMITS IN
        // BRACKETS)").
        $maxLimits = [
            'EQ' => '75',
            'PROP' => '25',
            'DEBT' => '50',
            'BOND' => '100',
            'COMM' => '10',
            'CASH' => '100',
        ];

        // The flexible fund shares the AA_DOM_* factsheet layout but is
        // unconstrained — its published fact sheet shows no mandate limits
        // ("ASSET ALLOCATION %", plain SA/FOREIGN headers).
        $isUnconstrained = ($fund->template ?? '') === 'show-flexible';

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
                'limit' => $isUnconstrained ? null : $maxLimits[$key],
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
            $assetAllocation['title'] = $assetAllocation['title'] ?? ($isUnconstrained ? 'ASSET ALLOCATION %' : 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)');
            $assetAllocation['subtitle'] = $assetAllocation['subtitle'] ?? 'Change since '.$this->formatChangeDate($data);
            $assetAllocation['headers'] = $isUnconstrained
                ? ['', 'SA', 'FOREIGN', 'TOTAL', 'CHANGE']
                : ['', 'SA (100)', 'FOREIGN (45)', 'TOTAL', 'CHANGE'];
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

    /**
     * Equity sector bars (ESAOT_*) → the sector_allocation column.
     *
     * The sheet carries the change magnitude but no sign, so the arrow
     * direction is derived by comparing against the previously stored value
     * for the same sector; unresolvable ties keep the prior direction.
     */
    private function mapEquitySectorAllocation(Fund $fund, array $data): void
    {
        $previous = collect($fund->sector_allocation['sectors'] ?? [])
            ->keyBy('name');

        $sectors = [];
        for ($i = 1; $i <= 13; $i++) {
            $item = $data["ESAOT_RANK_{$i}_ITEM"] ?? null;
            if (! $item) {
                continue;
            }

            $value = $this->toNumber($data["ESAOT_RANK_{$i}_CURRENT"] ?? 0);
            $prior = $previous->get($item);

            if ($prior !== null && is_numeric($prior['value'] ?? null) && $prior['value'] != $value) {
                $direction = $value > $prior['value'] ? 'up' : 'down';
            } else {
                $direction = $prior['direction'] ?? '';
            }

            $sectors[] = [
                'name' => $item,
                'value' => $value,
                'change' => number_format((float) ($data["ESAOT_RANK_{$i}_CHANGE"] ?? 0), 1),
                'direction' => $direction,
            ];
        }

        if (! $sectors) {
            return;
        }

        $sectorAllocation = $fund->sector_allocation ?? [];
        $sectorAllocation['sectors'] = $sectors;
        $sectorAllocation['title'] = $sectorAllocation['title'] ?? 'EQUITY SECTOR ALLOCATION %';

        $monthEnd = $this->parseMonthEnd($data);
        if ($monthEnd) {
            $sectorAllocation['subtitle'] = 'Change since '
                .$monthEnd->copy()->subMonthNoOverflow()->endOfMonth()->format('j F Y');
        }

        $fund->sector_allocation = $sectorAllocation;
    }

    private function mapGeographicExposure(Fund $fund, array $data): void
    {
        // Region display names and order per the published international
        // fact sheet (875 reference: North America, Europe, Pacific,
        // Emerging Asia, Africa & Middle East, EM Latin America).
        $regions = ['US', 'EUR', 'PAC', 'ASIA_EM', 'AFRME', 'LATAM_EM'];
        $regionNames = [
            'US' => 'North America',
            'EUR' => 'Europe',
            'PAC' => 'Pacific',
            'ASIA_EM' => 'Emerging Asia',
            'AFRME' => 'Africa & Middle East',
            'LATAM_EM' => 'EM Latin America',
        ];

        $rows = [];
        foreach ($regions as $region) {
            $total = $data["GEO_EXP_{$region}_TOTAL"] ?? null;
            if ($total === null || $total === '-') {
                continue;
            }
            $rows[] = [
                'name' => $regionNames[$region],
                'equity' => $this->dashToZero($data["GEO_EXP_{$region}_EQTY"] ?? '-'),
                'cash' => $this->dashToZero($data["GEO_EXP_{$region}_CASH"] ?? '-'),
                'total' => $this->toNumber($total),
            ];
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['geographicExposure'] = $rows;
            $assetAllocation['geographicTotals'] = [
                'name' => 'TOTAL',
                'total' => array_sum(array_column($rows, 'total')),
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
                'SA_TER_MANAGERS_CHARGE' => '— Manager’s charge (basic)',
                'SA_TER_PERFORMANCE_CHARGE' => '— Performance charge',
                'SA_TER_FOORD_GLOBAL_CHARGE' => '— Foord global charges',
                'SA_TER_VAT_AND_SUNDRY_COSTS' => '— VAT and sundry costs',
                'SA_TER_TRANSACTIONS_COSTS_INCL_VAT' => 'Transaction costs (incl VAT)',
            ];
            foreach ($mapping as $prefix => $name) {
                $m12 = $data["{$prefix}_12_MONTH"] ?? null;
                $m36 = $data["{$prefix}_36_MONTH"] ?? null;
                if ($m12 === null) {
                    continue;
                }
                // Funds without global exposure export zero global charges;
                // the fact sheets omit the row entirely — except the flexible
                // fund, whose published fact sheet lists the 0.00 row.
                if ($prefix === 'SA_TER_FOORD_GLOBAL_CHARGE'
                    && ($fund->template ?? '') !== 'show-flexible'
                    && $this->toNumber($m12) == 0.0
                    && ($m36 === null || $this->toNumber($m36) == 0.0)) {
                    continue;
                }
                $rows[] = [
                    'name' => $name,
                    '12m' => $this->toNumber($m12),
                    '36m' => $m36 !== null ? $this->toNumber($m36) : null,
                ];
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
                    "The TER for the fund\u{2019}s financial year ended {$yearEnd} was {$ter}.",
                    $desc
                );
                $tic['description'] = $desc;
                $fees['totalInvestmentCharge'] = $tic;
                $fund->fees = $fees;
            }
        }
    }

    /**
     * Keep the monthly-chart narrative's outperformance percentage in step
     * with the sheet (equity funds only; the paragraph itself is seeded once
     * per fund).
     */
    private function updateChartDescription(Fund $fund, array $data): void
    {
        $pct = $data['BETTER_THAN_ALSI_WHEN_ALSI_NEGATIVE'] ?? null;

        if ($pct === null || ! $fund->chart_description) {
            return;
        }

        $fund->chart_description = preg_replace(
            '/outperformed the benchmark \d+% of the time/',
            "outperformed the benchmark {$pct}% of the time",
            $fund->chart_description
        );
    }

    private function parseMonthEnd(array $data): ?Carbon
    {
        if (empty($data['MONTH_END_DATE'])) {
            return null;
        }

        try {
            return Carbon::parse($data['MONTH_END_DATE']);
        } catch (\Exception) {
            return null;
        }
    }

    private function formatChangeDate(array $data): string
    {
        $lastQuarter = $data['LAST_QUARTER_END'] ?? $data['MONTH_END_LESS_THREE'] ?? null;

        return $lastQuarter ?? '';
    }
}
