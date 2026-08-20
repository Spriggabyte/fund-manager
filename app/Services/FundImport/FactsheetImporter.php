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
        $this->mapMaturityBreakdown($fund, $data);
        $this->mapPortfolioStatistics($fund, $data);
        $this->mapCreditExposure($fund, $data);
        $this->mapMonthlyPerformance($fund, $data);
        $this->mapPerformanceTable($fund, $data);
        $this->mapTotalInvestmentCharge($fund, $data);
        $this->updateChartDescription($fund, $data);
    }

    /**
     * A feed cell that can't be used this month: absent, the export's ERR
     * marker, or empty. The bond fund's stats arrive as ERR some months, so
     * unusable cells preserve whatever was stored previously.
     */
    private function isUsable(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== 'ERR';
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
            // The published sheets space-separate thousands ("4 434"); the
            // 820 feed exports commas ("4,517").
            $fund->number_of_units = str_replace(',', ' ', $data['NUMBER_OF_UNITS']);
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
        // domestic-only (bare AA_TOTAL_* without the DOM/FRGN split) vs
        // international (AAOT_*) vs flex income (PS_* portfolio structure)
        if (isset($data['AA_SHARE_CURRENT'])) {
            $this->mapEquityAssetAllocation($fund, $data);
        } elseif (isset($data['AA_DOM_EQ']) || isset($data['AA_DOM_TOTAL'])) {
            $this->mapSaAssetAllocation($fund, $data);
        } elseif (isset($data['AA_TOTAL_EQ'])) {
            $this->mapDomesticAssetAllocation($fund, $data);
        } elseif (isset($data['AAOT_RANK_1_ITEM'])) {
            $this->mapGlobalAssetAllocation($fund, $data);
        } elseif (isset($data['PS_SA_TOTAL'])) {
            $this->mapPortfolioStructure($fund, $data);
        }
    }

    /**
     * Flex-income portfolio structure (PS_*) — the SA/FOREIGN/TOTAL/CHANGE
     * table with per-row change arrows, plus the foreign currency hedge and
     * exposure rows beneath the total (824 reference).
     *
     * The income fund (825) shares the PS_* keys but its published table has
     * no SA/FOREIGN split — a single month-end value column plus the change
     * arrows, fixed to the reference's six asset classes, and no foreign
     * currency rows (foreign assets are N/A).
     *
     * Values are stored as display strings: the published sheet prints "-"
     * for empty AND zero holdings (e.g. Preference shares 0 → "-") while
     * negative effective exposures ("-5") print as-is. Change signs come from
     * the PS_TOTAL_CHANGE_SIGN_* keys; rows without a sign print a bare "-".
     */
    private function mapPortfolioStructure(Fund $fund, array $data): void
    {
        $isIncome = ($fund->template ?? '') === 'show-income';

        $categories = [
            'CASH_AND_CALL' => 'Cash and call',
            'MONEY_MARKET' => 'Money market',
            'FLOATING_RATE_NOTES' => 'Floating rate notes',
            'FIXED_RATE_BONDS' => 'Fixed rate bonds',
            'FIXED_RATE_NCDS' => 'Fixed rate NCDs',
            'INFLATION_LINKED_BONDS' => 'Inflation linked bonds',
        ];
        if (! $isIncome) {
            $categories += [
                'PREFERENCE_SHARES' => 'Preference shares',
                'CONVERTIBLE_BONDS' => 'Convertible bonds',
                'PROPERTY' => 'Property',
                'EQTY' => 'Equity',
            ];
        }

        $display = function (mixed $value): string {
            if ($value === null || $value === '' || $value === '-') {
                return '-';
            }

            return is_numeric($value) && (float) $value == 0.0 ? '-' : (string) $value;
        };

        $rows = [];
        foreach ($categories as $key => $name) {
            if (! array_key_exists("PS_TOTAL_{$key}", $data)) {
                continue;
            }

            $change = $data["PS_TOTAL_CHANGE_{$key}"] ?? '-';
            $sign = trim((string) ($data["PS_TOTAL_CHANGE_SIGN_{$key}"] ?? ''));

            if ($sign === '' || ! is_numeric($change)) {
                $changeDisplay = '-';
                $direction = '';
            } else {
                $changeDisplay = ($sign === '-' ? '▼ ' : '▲ ').number_format((float) $change, 1);
                $direction = $sign === '-' ? 'down' : 'up';
            }

            if ($isIncome) {
                $rows[] = [
                    'name' => $name,
                    'value' => $display($data["PS_TOTAL_{$key}"] ?? '-'),
                    'change' => $changeDisplay,
                    'changeDirection' => $direction,
                ];
            } else {
                $rows[] = [
                    'name' => $name,
                    'sa' => $display($data["PS_SA_{$key}"] ?? '-'),
                    'foreign' => $display($data["PS_FOREIGN_{$key}"] ?? '-'),
                    'total' => $display($data["PS_TOTAL_{$key}"] ?? '-'),
                    'change' => $changeDisplay,
                    'changeDirection' => $direction,
                ];
            }
        }

        if (! $rows) {
            return;
        }

        $assetAllocation = $fund->asset_allocation ?? [];
        $assetAllocation['rows'] = $rows;
        $assetAllocation['title'] = $assetAllocation['title'] ?? 'PORTFOLIO STRUCTURE %';
        if ($this->formatChangeDate($data) !== '') {
            $assetAllocation['subtitle'] = 'Change since '.$this->formatChangeDate($data);
        }

        if ($isIncome) {
            // Value column headed by the month-end date ("31 JUL 2026").
            $monthEnd = $this->parseMonthEnd($data);
            $assetAllocation['headers'] = [
                'ASSET CLASS',
                $monthEnd ? strtoupper($monthEnd->format('j M Y')) : '',
                'CHANGE',
            ];
            $assetAllocation['total'] = ['name' => 'TOTAL', 'value' => '100', 'change' => ''];
            $fund->asset_allocation = $assetAllocation;

            return;
        }

        $assetAllocation['headers'] = ['', 'SA', 'FOREIGN', 'TOTAL', 'CHANGE'];
        $assetAllocation['total'] = [
            'name' => 'TOTAL',
            'sa' => $display($data['PS_SA_TOTAL'] ?? '-'),
            'foreign' => $display($data['PS_FOREIGN_TOTAL'] ?? '-'),
            'total' => '100',
            'change' => '',
        ];

        // The hedge prints in accounting brackets ("(6)"); both rows sit
        // under the FOREIGN column on the published sheet.
        $hedge = $data['FOREIGN_CURRENCY_HEDGE'] ?? null;
        if ($hedge !== null) {
            $hedge = $display($hedge);
            $assetAllocation['foreignCurrencyHedge'] = $hedge === '-' ? '-' : "({$hedge})";
        }
        $exposure = $data['FOREIGN_CURRENCY_EXPOSURE'] ?? null;
        if ($exposure !== null) {
            $assetAllocation['foreignCurrencyExposure'] = $display($exposure);
        }

        $fund->asset_allocation = $assetAllocation;
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

        // The conservative fund's mandate caps equities at 60% ("Equities (60)"
        // on the published fact sheet); the other Reg 28 categories share the
        // balanced fund's limits.
        if (($fund->template ?? '') === 'show-conservative') {
            $maxLimits['EQ'] = '60';
        }

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

    /**
     * Domestic-only funds (820) hold no foreign assets, so their factsheet
     * exports bare AA_TOTAL_* keys with no AA_DOM_/AA_FRGN_ split. The
     * published sheet shows a single "SA (100)" column plus the change
     * arrows, and omits asset classes with no holding (the 820 reference
     * lists no Corporate bonds row while AA_TOTAL_DEBT exports 0.0).
     */
    private function mapDomesticAssetAllocation(Fund $fund, array $data): void
    {
        $categories = [
            'EQ' => 'Equities',
            'PROP' => 'Listed property',
            'DEBT' => 'Corporate bonds',
            'BOND' => 'Government bonds',
            'COMM' => 'Commodities',
            'CASH' => 'Money market',
        ];

        $maxLimits = [
            'EQ' => '75',
            'PROP' => '25',
            'DEBT' => '50',
            'BOND' => '100',
            'COMM' => '10',
            'CASH' => '100',
        ];

        $rows = [];
        foreach ($categories as $key => $name) {
            $total = $data["AA_TOTAL_{$key}"] ?? null;
            if ($total === null || $this->toNumber($total) == 0.0) {
                continue;
            }

            $change = $data["AA_TOTAL_DIFF_{$key}"] ?? '0';
            $sign = $data["AA_TOTAL_DIFF_SIGN_{$key}"] ?? '+';

            $rows[] = [
                'name' => $name,
                'limit' => $maxLimits[$key],
                'sa' => $this->toNumber($total),
                'change' => ($sign === '-' ? '▼ ' : '▲ ').$change,
                'changeDirection' => $sign === '-' ? 'down' : 'up',
            ];
        }

        if (! $rows) {
            return;
        }

        $assetAllocation = $fund->asset_allocation ?? [];
        $assetAllocation['rows'] = $rows;
        $assetAllocation['title'] = $assetAllocation['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)';
        if ($this->formatChangeDate($data) !== '') {
            $assetAllocation['subtitle'] = 'Change since '.$this->formatChangeDate($data);
        }
        $assetAllocation['headers'] = ['', 'SA (100)', 'CHANGE'];
        $assetAllocation['total'] = ['name' => 'TOTAL', 'sa' => 100, 'change' => ''];
        $fund->asset_allocation = $assetAllocation;
    }

    private function mapGlobalAssetAllocation(Fund $fund, array $data): void
    {
        $previous = collect($fund->asset_allocation['rows'] ?? [])->keyBy('name');

        // The sheets have carried two CHANGE semantics: older exports a
        // genuine signed delta ("2.4", sign "-"), recent exports the precise
        // unrounded current value ("69.5" against CURRENT 69) with a constant
        // "+" sign. When every populated change rounds to its own CURRENT the
        // sheet is precise-current, and the published delta must be derived
        // against the previously stored precise value instead.
        $preciseSemantics = null;
        for ($i = 1; $i <= 8; $i++) {
            $current = $data["AAOT_RANK_{$i}_CURRENT"] ?? null;
            $change = $data["AAOT_RANK_{$i}_CHANGE"] ?? null;
            if (! ($data["AAOT_RANK_{$i}_ITEM"] ?? null) || $current === null || $current === '-' || $change === null) {
                continue;
            }
            if (abs($this->toNumber($change) - $this->toNumber($current)) > 0.5) {
                $preciseSemantics = false;
                break;
            }
            $preciseSemantics ??= true;
        }

        // The fact sheets report change since the last quarter end, so the
        // stored comparison baseline only rolls forward on quarter-end sheets
        // (or when no baseline exists yet).
        $monthEnd = $this->parseMonthEnd($data);
        $isQuarterEnd = $monthEnd !== null && $monthEnd->month % 3 === 0;

        $rows = [];
        for ($i = 1; $i <= 8; $i++) {
            $item = $data["AAOT_RANK_{$i}_ITEM"] ?? null;
            if (! $item) {
                continue;
            }

            $current = $data["AAOT_RANK_{$i}_CURRENT"] ?? '-';
            $change = $data["AAOT_RANK_{$i}_CHANGE"] ?? '0';
            $sign = $data["AAOT_RANK_{$i}_CHANGE_SIGN"] ?? '+';

            $row = [
                'name' => $item,
                'total' => $current === '-' ? 0 : $this->toNumber($current),
            ];

            if ($preciseSemantics) {
                $precise = $this->toNumber($change);
                $baseline = $previous->get($item)['precise'] ?? null;
                if ($baseline !== null) {
                    $delta = $precise - (float) $baseline;
                    $row['change'] = ($delta < 0 ? '▼ ' : '▲ ').number_format(abs($delta), 1);
                    $row['changeDirection'] = $delta < 0 ? 'down' : 'up';
                } else {
                    $row['change'] = '';
                    $row['changeDirection'] = '';
                }
                $row['precise'] = ($isQuarterEnd || $baseline === null) ? $precise : (float) $baseline;
            } else {
                $row['change'] = ($sign === '-' ? '▼ ' : '▲ ').$change;
                $row['changeDirection'] = $sign === '-' ? 'down' : 'up';
            }

            $rows[] = $row;
        }

        if ($rows) {
            $assetAllocation = $fund->asset_allocation ?? [];
            $assetAllocation['rows'] = $rows;
            $assetAllocation['title'] = $assetAllocation['title'] ?? 'ASSET ALLOCATION %';
            $assetAllocation['headers'] = ['', 'TOTAL', 'CHANGE'];
            if ($this->formatChangeDate($data) !== '') {
                $assetAllocation['subtitle'] = 'Change since '.$this->formatChangeDate($data);
            }
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

    /**
     * Bond-fund maturity breakdown (MATURITY_*) → chart_data['maturityData']
     * for the grouped Fund-vs-Benchmark bar chart.
     *
     * The feed only carries the fund's buckets — the ALBI benchmark
     * composition is maintained by hand and preserved across imports. The
     * per-bucket change labels (MAT_CHANGE_*) export as ERR some months, in
     * which case the previously stored label is kept.
     */
    private function mapMaturityBreakdown(Fund $fund, array $data): void
    {
        if (! isset($data['MATURITY_0_TO_1_YEAR'])) {
            return;
        }

        // The flex income and income sheets export the same MATURITY_* keys
        // but their published charts are horizontal MATURITY SPREAD % bar
        // lists (using the 12+ bucket and Perpetual, no benchmark or change
        // labels).
        if (in_array($fund->template ?? '', ['show-flex-income', 'show-income'], true)) {
            $this->mapMaturitySpread($fund, $data);

            return;
        }

        $buckets = [
            'MATURITY_0_TO_1_YEAR' => ['0-1 Year', 'MAT_CHANGE_0_TO_1_YEARS'],
            'MATURITY_1_TO_3_YEARS' => ['1-3 Years', 'MAT_CHANGE_1_TO_3_YEARS'],
            'MATURITY_3_TO_7_YEARS' => ['3-7 Years', 'MAT_CHANGE_3_TO_7_YEARS'],
            'MATURITY_7_TO_12_YEARS' => ['7-12 Years', 'MAT_CHANGE_7_TO_12_YEARS'],
            'MATURITY_12_TO_20_YEARS' => ['12-20 Years', 'MAT_CHANGE_12_TO_20_YEARS'],
            'MATURITY_20_PLUS_YEARS' => ['20+ Years', 'MAT_CHANGE_20_PLUS_YEARS'],
        ];

        $chartData = $fund->chart_data ?? [];
        $previous = collect($chartData['maturityData']['categories'] ?? [])->keyBy('name');

        $categories = [];
        foreach ($buckets as $key => [$label, $changeKey]) {
            $prior = $previous->get($label) ?? [];
            $change = $data[$changeKey] ?? null;

            $categories[] = [
                'name' => $label,
                'fund' => $this->dashToZero((string) ($data[$key] ?? '-')),
                'benchmark' => $prior['benchmark'] ?? null,
                'change' => $this->isUsable($change) && $change !== '-'
                    ? $this->formatChangeLabel($change)
                    : ($prior['change'] ?? ''),
            ];
        }

        $maturity = $chartData['maturityData'] ?? [];
        $maturity['title'] = $maturity['title'] ?? 'MATURITY BREAKDOWN';
        if ($this->formatChangeDate($data) !== '') {
            $maturity['subtitle'] = 'Change since '.$this->formatChangeDate($data);
        }
        $maturity['categories'] = $categories;

        $chartData['maturityData'] = $maturity;
        $fund->chart_data = $chartData;
    }

    /**
     * Flex-income maturity spread (MATURITY_*) → chart_data['maturitySpread']
     * for the horizontal naartjie bar list (824 reference). Zero or absent
     * buckets keep a zero-length bar labelled "-", like the published sheet.
     */
    private function mapMaturitySpread(Fund $fund, array $data): void
    {
        $buckets = [
            'MATURITY_0_TO_1_YEAR' => '0—1 years',
            'MATURITY_1_TO_3_YEARS' => '1—3 years',
            'MATURITY_3_TO_7_YEARS' => '3—7 years',
            'MATURITY_7_TO_12_YEARS' => '7—12 years',
        ];

        $categories = [];
        foreach ($buckets as $key => $label) {
            $value = $this->dashToZero((string) ($data[$key] ?? '-'));
            $categories[] = [
                'name' => $label,
                'value' => $value,
                'label' => $value == 0 ? '-' : (string) $value,
            ];
        }

        if (($fund->template ?? '') === 'show-income') {
            // The 825 reference has no Perpetual row — perpetual paper folds
            // into the "> 12 years" bucket.
            $value = $this->dashToZero((string) ($data['MATURITY_12_PLUS_YEARS'] ?? '-'))
                + $this->dashToZero((string) ($data['MATURITY_PERPETUAL'] ?? '-'));
            $categories[] = [
                'name' => '> 12 years',
                'value' => $value,
                'label' => $value == 0 ? '-' : (string) $value,
            ];
        } else {
            foreach (['MATURITY_12_PLUS_YEARS' => '> 12 years', 'MATURITY_PERPETUAL' => 'Perpetual'] as $key => $label) {
                $value = $this->dashToZero((string) ($data[$key] ?? '-'));
                $categories[] = [
                    'name' => $label,
                    'value' => $value,
                    'label' => $value == 0 ? '-' : (string) $value,
                ];
            }
        }

        $chartData = $fund->chart_data ?? [];
        $spread = $chartData['maturitySpread'] ?? [];
        $spread['title'] = $spread['title'] ?? 'MATURITY SPREAD %';
        $spread['categories'] = $categories;

        $chartData['maturitySpread'] = $spread;
        $fund->chart_data = $chartData;
    }

    /**
     * The published chart prints the quarter-on-quarter change beneath each
     * maturity bucket as "(+22.2%)" / "(-7.2%)".
     */
    private function formatChangeLabel(mixed $change): string
    {
        if (is_numeric($change)) {
            return sprintf('(%s%s%%)', $change >= 0 ? '+' : '', $change + 0);
        }

        $change = trim((string) $change);

        return str_starts_with($change, '(') ? $change : "({$change})";
    }

    /**
     * Bond-fund portfolio statistics (STAT_* / BM_* / VAR_TO_BM_*) →
     * asset_allocation['portfolioStatistics'].
     *
     * Values are stored as display strings ("10.17%", "11.98 years", "6.66");
     * ERR cells keep the previously stored value so a broken feed month never
     * blanks the published table.
     */
    private function mapPortfolioStatistics(Fund $fund, array $data): void
    {
        if (! isset($data['STAT_YIELD'])) {
            return;
        }

        // The flex income export carries its own single-column statistics
        // layout (spread to JIBAR + SA/offshore duration split, no benchmark).
        if (isset($data['STAT_SPREAD_TO_JIBAR'])) {
            $this->mapFlexPortfolioStatistics($fund, $data);

            return;
        }

        $rowDefs = [
            ['name' => 'Yield', 'sup' => '1', 'format' => 'percent',
                'fund' => 'STAT_YIELD', 'benchmark' => 'BM_YIELD', 'relative' => null],
            ['name' => 'Weighted average time to maturity', 'format' => 'years',
                'fund' => 'STAT_WEIGHTED_AVERAGE_TTM', 'benchmark' => 'BM_WEIGHTED_AVERAGE_TTM', 'relative' => null],
            ['spacer' => true],
            ['name' => 'Total duration', 'sup' => '2',
                'fund' => 'STAT_SA_DURATION', 'benchmark' => 'BM_SA_DURATION', 'relative' => 'VAR_TO_BM_SA_DURATION'],
            ['name' => '— Fixed rate duration',
                'fund' => 'STAT_SA_FIXED_RATE_DURATION', 'benchmark' => 'BM_SA_FIXED_RATE_DURATION_BOND', 'relative' => 'VAR_TO_BM_SA_FIXED_RATE_DURATION_BOND'],
            ['name' => '— Inflation linked duration',
                'fund' => 'STAT_SA_INFLATION_LINKED_DURATION', 'benchmark' => 'BM_SA_INFLATION_LINKED_DURATION', 'relative' => 'VAR_TO_BM_SA_INFLATION_LINKED_DURATION'],
            // The ALBI holds no floating-rate paper, so the published relative
            // column repeats the fund's own duration (no VAR_* key on the feed).
            ['name' => '— Floating rate duration',
                'fund' => 'STAT_SA_FLOATING_RATE_DURATION', 'benchmark' => null, 'relative' => 'STAT_SA_FLOATING_RATE_DURATION'],
        ];

        $assetAllocation = $fund->asset_allocation ?? [];
        $stats = $assetAllocation['portfolioStatistics'] ?? [];
        $previous = collect($stats['rows'] ?? [])->keyBy(fn ($row) => $row['name'] ?? '');

        $rows = [];
        foreach ($rowDefs as $def) {
            if ($def['spacer'] ?? false) {
                $rows[] = ['spacer' => true];

                continue;
            }

            $prior = $previous->get($def['name']) ?? [];
            $row = ['name' => $def['name']];
            if (isset($def['sup'])) {
                $row['sup'] = $def['sup'];
            }

            foreach (['fund', 'benchmark', 'relative'] as $col) {
                $key = $def[$col];
                if ($key !== null && $this->isUsable($data[$key] ?? null)) {
                    $row[$col] = $this->formatStatValue($data[$key], $def['format'] ?? 'duration');
                } else {
                    // No feed key for this cell, or an ERR month — keep the
                    // stored value (blank when nothing has been stored yet).
                    $row[$col] = $prior[$col] ?? ($key === null ? '' : '-');
                }
            }

            $rows[] = $row;
        }

        $stats['title'] = $stats['title'] ?? 'PORTFOLIO STATISTICS';
        $stats['headers'] = $stats['headers'] ?? ['', 'FUND', 'BENCHMARK', 'RELATIVE TO ALBI'];
        $stats['rows'] = $rows;

        $assetAllocation['portfolioStatistics'] = $stats;
        $fund->asset_allocation = $assetAllocation;
    }

    /**
     * Flex-income portfolio statistics (STAT_*) — a two-column label/value
     * table (824 reference): Yield and Spread to JIBAR as percentages, then
     * the SA and Offshore duration groups with their em-dashed sub-rows.
     * The income fund (825) shares the keys but has no offshore assets, so
     * its published table stops after the SA duration group and drops the
     * "SA" prefix from the sub-row labels (825 reference).
     *
     * The 824/825 feeds export ERR for every STAT_ key some months; unusable
     * cells preserve the previously stored value (seeded from the published
     * reference) so a broken feed month never blanks the table.
     */
    private function mapFlexPortfolioStatistics(Fund $fund, array $data): void
    {
        if (($fund->template ?? '') === 'show-income') {
            $rowDefs = [
                ['name' => 'Yield', 'sup' => '1', 'key' => 'STAT_YIELD', 'format' => 'percent'],
                ['name' => 'Spread to JIBAR', 'key' => 'STAT_SPREAD_TO_JIBAR', 'format' => 'percent'],
                ['spacer' => true],
                ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'key' => 'STAT_SA_DURATION'],
                ['name' => '— Fixed rate duration', 'key' => 'STAT_SA_FIXED_RATE_DURATION'],
                ['name' => '— Floating rate duration', 'key' => 'STAT_SA_FLOATING_RATE_DURATION'],
                ['name' => '— Inflation linked duration', 'key' => 'STAT_SA_INFLATION_LINKED_DURATION'],
            ];
        } else {
            $rowDefs = [
                ['name' => 'Yield', 'sup' => '1', 'key' => 'STAT_YIELD', 'format' => 'percent'],
                ['name' => 'Spread to JIBAR', 'key' => 'STAT_SPREAD_TO_JIBAR', 'format' => 'percent'],
                ['spacer' => true],
                ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'key' => 'STAT_SA_DURATION'],
                ['name' => '— SA fixed rate duration', 'key' => 'STAT_SA_FIXED_RATE_DURATION'],
                ['name' => '— SA floating rate duration', 'key' => 'STAT_SA_FLOATING_RATE_DURATION'],
                ['name' => '— SA inflation linked duration', 'key' => 'STAT_SA_INFLATION_LINKED_DURATION'],
                ['name' => 'Offshore duration', 'sup' => '2', 'bold' => true, 'key' => 'STAT_FOREIGN_DURATION'],
                ['name' => '— Offshore fixed rate duration', 'key' => 'STAT_FOREIGN_FIXED_RATE_DURATION'],
                ['name' => '— Offshore inflation linked', 'key' => 'STAT_FOREIGN_INFLATION_LINKED_DURATION'],
            ];
        }

        $assetAllocation = $fund->asset_allocation ?? [];
        $stats = $assetAllocation['portfolioStatistics'] ?? [];
        $previous = collect($stats['rows'] ?? [])->keyBy(fn ($row) => $row['name'] ?? '');

        $rows = [];
        foreach ($rowDefs as $def) {
            if ($def['spacer'] ?? false) {
                $rows[] = ['spacer' => true];

                continue;
            }

            $prior = $previous->get($def['name']) ?? [];
            $row = ['name' => $def['name']];
            if (isset($def['sup'])) {
                $row['sup'] = $def['sup'];
            }
            if ($def['bold'] ?? false) {
                $row['bold'] = true;
            }

            $row['value'] = $this->isUsable($data[$def['key']] ?? null)
                ? $this->formatStatValue($data[$def['key']], $def['format'] ?? 'duration')
                : ($prior['value'] ?? '');

            $rows[] = $row;
        }

        $stats['title'] = $stats['title'] ?? 'PORTFOLIO STATISTICS';
        $stats['rows'] = $rows;
        unset($stats['headers']);

        $assetAllocation['portfolioStatistics'] = $stats;
        $fund->asset_allocation = $assetAllocation;
    }

    private function formatStatValue(mixed $value, string $format): string
    {
        if ($value === '-') {
            return '-';
        }
        if (! is_numeric($value)) {
            return (string) $value;
        }

        $formatted = number_format((float) $value, 2);

        return match ($format) {
            'percent' => $formatted.'%',
            'years' => $formatted.' years',
            default => $formatted,
        };
    }

    /**
     * Bond-fund credit exposure (RATING_* / SECTOR_*) →
     * asset_allocation['creditExposure'] — the two side-by-side tables.
     *
     * The rating table keeps its full fixed row list (dashes print as "-");
     * the sector table only lists sectors with exposure, per the published
     * fact sheet. Values are effective exposures, so negatives and values
     * above 100 are legitimate.
     */
    private function mapCreditExposure(Fund $fund, array $data): void
    {
        if (! isset($data['RATING_AAA'])) {
            return;
        }

        $ratings = [];
        foreach ([
            'RATING_F1_PLUS' => 'F1+',
            'RATING_F1' => 'F1',
            'RATING_AAA' => 'AAA',
            'RATING_AA' => 'AA',
            'RATING_A' => 'A',
            'RATING_OTHER' => 'Other',
        ] as $key => $label) {
            $ratings[] = ['name' => $label, 'value' => (string) ($data[$key] ?? '-')];
        }

        $sectors = [];
        foreach ([
            'SECTOR_BANK' => 'Big four banks',
            'SECTOR_CORP' => 'SA Corporates',
            'SECTOR_RSA' => 'SA Government',
            'SECTOR_USGOV' => 'US Government',
            'SECTOR_OTHER' => 'Other',
        ] as $key => $label) {
            $value = $data[$key] ?? '-';
            if ($value === '-') {
                continue;
            }
            $sectors[] = ['name' => $label, 'value' => (string) $value];
        }

        $assetAllocation = $fund->asset_allocation ?? [];
        $credit = $assetAllocation['creditExposure'] ?? [];
        $credit['title'] = $credit['title'] ?? 'CREDIT EXPOSURE BREAKDOWN %';
        $credit['ratings'] = $ratings;
        $credit['sectors'] = $sectors;

        $assetAllocation['creditExposure'] = $credit;
        $fund->asset_allocation = $assetAllocation;
    }

    /**
     * Bond-fund monthly return grid (YEAR_n_MONTH_* / YEAR_n_YTD) →
     * chart_data['monthlyPerformance'] for the page-2 MONTHLY PERFORMANCE %
     * table. YEAR_1 is the inception year; the last populated year is the
     * sheet's month-end year, which anchors the calendar-year labels.
     */
    private function mapMonthlyPerformance(Fund $fund, array $data): void
    {
        $yearNumbers = [];
        for ($n = 1; $n <= 10; $n++) {
            if (array_key_exists("YEAR_{$n}_YTD", $data)) {
                $yearNumbers[] = $n;
            }
        }

        $monthEnd = $this->parseMonthEnd($data);
        if ($yearNumbers === [] || $monthEnd === null) {
            return;
        }

        $lastN = end($yearNumbers);
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        $years = [];
        foreach ($yearNumbers as $n) {
            $row = ['year' => (string) ($monthEnd->year - ($lastN - $n)), 'months' => []];
            foreach ($months as $month) {
                $value = $data['YEAR_'.$n.'_MONTH_'.strtoupper($month)] ?? null;
                $row['months'][$month] = $this->isUsable($value) && is_numeric($value)
                    ? number_format((float) $value, 2)
                    : null;
            }
            $ytd = $data["YEAR_{$n}_YTD"] ?? null;
            $row['ytd'] = $this->isUsable($ytd) ? (string) $ytd : null;
            $years[] = $row;
        }

        $chartData = $fund->chart_data ?? [];
        $monthly = $chartData['monthlyPerformance'] ?? [];
        $monthly['title'] = $monthly['title'] ?? 'MONTHLY PERFORMANCE %';
        $monthly['years'] = $years;

        $chartData['monthlyPerformance'] = $monthly;
        $fund->chart_data = $chartData;
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

        // The conservative fund's Stats SA footnote dates its CPI estimate
        // ("… performance as calculated by Foord (estimated for March 2026)");
        // keep the month in step with the sheet, like the TER footnote.
        $monthLabel = $data['MONTH_END_DATE_MMMM_YYYY'] ?? null;
        if ($monthLabel && isset($performanceTable['footnotes'])) {
            $performanceTable['footnotes'] = array_map(
                fn (string $note) => preg_replace('/\(estimated for [A-Za-z]+ \d{4}\)/', "(estimated for {$monthLabel})", $note),
                $performanceTable['footnotes']
            );
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
                // The bond, flex income and income funds levy no performance
                // fee; their published fact sheets omit the zero row
                // (826/824/825 references).
                if ($prefix === 'SA_TER_PERFORMANCE_CHARGE'
                    && in_array($fund->template ?? '', ['show-bond', 'show-flex-income', 'show-income'], true)
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
