<?php

namespace App\Services\FundImport;

use App\Models\Fund;

/**
 * LOCAL_OVERVIEW.xlsx → the FUND OVERVIEW: SOUTH AFRICA record.
 *
 * One export covers five funds' performance (818, 820, 810, 817, 811), five
 * funds' asset allocation (824 + the four Reg 28/flexible funds), three
 * fixed-income funds' statistics (825, 824, 826 + ALBI), the bond fund's
 * maturity chart, the equity fund's sector chart and the quarterly market
 * synopsis / strategy bullets. Every key is prefixed with its fund code
 * (818_FOORD_10Y_TO_D, 824_AA_DOM_CASH) except the bullets and
 * MONTH_END_DATE.
 *
 * The importer owns every table skeleton — groups, fund codes, row keys,
 * column order — and only merges prose (labels, titles, footnotes, QR text)
 * back from the stored record by code/key, so a seed can set prose once
 * and monthly imports keep it. Missing values are absent keys, never
 * placeholder strings; the template decides how to print a gap.
 *
 * Cell parsing, the period map, the sector-list merge and the bullet
 * blocks live in AbstractOverviewImporter, shared with the global sheet.
 */
class LocalOverviewImporter extends AbstractOverviewImporter
{
    /**
     * Performance grid skeleton. Prose defaults (name, className, peer and
     * benchmark labels) are overridden by whatever the record already holds
     * for the same fund code.
     *
     * @var list<array{label: string, funds: list<array{code: string, name: string, className: string, peerLabel: string, benchmarkLabel: string}>}>
     */
    private const PERFORMANCE_GROUPS = [
        ['label' => 'REGULATION 28', 'funds' => [
            ['code' => '818', 'name' => 'Foord Conservative', 'className' => 'Class B2',
                'peerLabel' => 'Peer group: South Africa – Multi Asset – Medium Equity',
                'benchmarkLabel' => 'Benchmark: CPI + 4% per annum'],
            ['code' => '820', 'name' => 'Foord Domestic Balanced', 'className' => 'Class B2',
                'peerLabel' => 'Peer group: South Africa – Multi Asset – SA High Equity',
                'benchmarkLabel' => 'Benchmark: Average peer group excluding Foord'],
            ['code' => '810', 'name' => 'Foord Balanced', 'className' => 'Class B2',
                'peerLabel' => 'Peer group: South Africa – Multi Asset – High Equity',
                'benchmarkLabel' => 'Benchmark: MV weighted peer group excluding Foord'],
        ]],
        ['label' => 'BEST INVESTMENT VIEW', 'funds' => [
            ['code' => '817', 'name' => 'Foord Flexible', 'className' => 'Class B2',
                'peerLabel' => 'Peer group: Worldwide Multi Asset – Flexible',
                'benchmarkLabel' => 'Benchmark: CPI + 5% per annum'],
        ]],
        ['label' => 'SPECIALIST EQUITY', 'funds' => [
            ['code' => '811', 'name' => 'Foord Equity', 'className' => 'Class B2',
                'peerLabel' => 'Peer group: South Africa – Equity – General (SA only)',
                'benchmarkLabel' => 'Benchmark: FTSE / JSE Capped All Share index'],
        ]],
    ];

    /** @var list<string> */
    private const PERFORMANCE_FOOTNOTES = [
        'Source: Foord, Stats SA, Morningstar (Peer group: provisional)',
        'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
    ];

    /** @var list<array{code: string, label: string}> */
    private const AA_COLUMNS = [
        ['code' => '824', 'label' => 'FOORD<br>FLEX INCOME'],
        ['code' => '818', 'label' => 'FOORD<br>CONSERVATIVE'],
        ['code' => '820', 'label' => 'FOORD<br>DOMESTIC<br>BALANCED'],
        ['code' => '810', 'label' => 'FOORD<br>BALANCED'],
        ['code' => '817', 'label' => 'FOORD<br>FLEXIBLE'],
    ];

    /**
     * Asset-allocation rows in print order. `feed` is the {code}_AA_ suffix;
     * the TOTAL row has no feed key and is DOM_TOTAL + FRGN_TOTAL.
     *
     * @var list<array{key: string, label: string, feed: ?string, style?: string}>
     */
    private const AA_ROWS = [
        ['key' => 'saTotal', 'label' => 'SOUTH AFRICA', 'feed' => 'DOM_TOTAL', 'style' => 'subtotal'],
        ['key' => 'domEq', 'label' => 'Equities', 'feed' => 'DOM_EQ'],
        ['key' => 'domProp', 'label' => 'Property', 'feed' => 'DOM_PROP'],
        ['key' => 'domDebt', 'label' => 'Corporate bonds', 'feed' => 'DOM_DEBT'],
        ['key' => 'domBond', 'label' => 'Government bonds', 'feed' => 'DOM_BOND'],
        ['key' => 'domComm', 'label' => 'Commodities', 'feed' => 'DOM_COMM'],
        ['key' => 'domCash', 'label' => 'Money market', 'feed' => 'DOM_CASH'],
        ['key' => 'foreignTotal', 'label' => 'FOREIGN', 'feed' => 'FRGN_TOTAL', 'style' => 'subtotal'],
        ['key' => 'frgnEq', 'label' => 'Equities', 'feed' => 'FRGN_EQ'],
        ['key' => 'frgnProp', 'label' => 'Property', 'feed' => 'FRGN_PROP'],
        ['key' => 'frgnDebt', 'label' => 'Corporate bonds', 'feed' => 'FRGN_DEBT'],
        ['key' => 'frgnBond', 'label' => 'Government bonds', 'feed' => 'FRGN_BOND'],
        ['key' => 'frgnComm', 'label' => 'Commodities', 'feed' => 'FRGN_COMM'],
        ['key' => 'frgnCash', 'label' => 'Money market', 'feed' => 'FRGN_CASH'],
        ['key' => 'total', 'label' => 'TOTAL', 'feed' => null, 'style' => 'total'],
        ['key' => 'totalEq', 'label' => 'TOTAL EQUITY EXPOSURE', 'feed' => 'TOTAL_EQ', 'style' => 'subtotal'],
    ];

    /** @var list<array{code: string, label: string}> */
    private const STAT_COLUMNS = [
        ['code' => '825', 'label' => 'FOORD<br>INCOME'],
        ['code' => '824', 'label' => 'FOORD<br>FLEX<br>INCOME'],
        ['code' => '826', 'label' => 'FOORD<br>BOND'],
        ['code' => '826_BM', 'label' => 'ALBI'],
    ];

    /**
     * Fixed-income statistics rows. `feed` is the {code}_STAT_ suffix for the
     * three funds; `albi` the 826_BM_ suffix (null → the index has no such
     * statistic and prints "-").
     *
     * @var list<array{key: string, label: string, feed: string, albi: ?string, style?: string}>
     */
    private const STAT_ROWS = [
        ['key' => 'yield', 'label' => 'Yield', 'feed' => 'YIELD', 'albi' => 'YIELD'],
        ['key' => 'saFixed', 'label' => 'SA fixed rate duration', 'feed' => 'SA_FIXED_RATE_DURATION', 'albi' => 'SA_FIXED_RATE_DURATION_BOND'],
        ['key' => 'saFloating', 'label' => 'SA floating rate duration', 'feed' => 'SA_FLOATING_RATE_DURATION', 'albi' => null],
        ['key' => 'saInflation', 'label' => 'SA inflation linked duration', 'feed' => 'SA_INFLATION_LINKED_DURATION', 'albi' => 'SA_INFLATION_LINKED_DURATION'],
        ['key' => 'offshoreFixed', 'label' => 'Offshore fixed rate duration', 'feed' => 'FOREIGN_FIXED_RATE_DURATION', 'albi' => null],
        ['key' => 'offshoreInflation', 'label' => 'Offshore inflation linked duration', 'feed' => 'FOREIGN_INFLATION_LINKED_DURATION', 'albi' => null],
        ['key' => 'totalDuration', 'label' => 'TOTAL DURATION', 'feed' => 'TOTAL_DURATION', 'albi' => 'SA_DURATION', 'style' => 'total'],
    ];

    /** @var array<string, string> feed suffix => bucket label */
    private const MATURITY_BUCKETS = [
        '0_TO_1_YEAR' => '0-1 Year',
        '1_TO_3_YEARS' => '1-3 Years',
        '3_TO_7_YEARS' => '3-7 Years',
        '7_TO_12_YEARS' => '7-12 Years',
        '12_TO_20_YEARS' => '12-20 Years',
        '20_PLUS_YEARS' => '20+ Years',
    ];

    /** @var array{heading: string, text: string, url: string, image: string} */
    private const QR = [
        'heading' => 'SCAN QR CODE',
        'text' => 'to read regulatory disclosures or visit:',
        'url' => 'https://foord.co.za/terms-conditions-sa',
        'image' => 'images/qr-terms-sa.png',
    ];

    public function supports(string $filename): bool
    {
        return str_starts_with(strtoupper(basename($filename)), 'LOCAL_OVERVIEW');
    }

    public function label(): string
    {
        return 'local fund overview';
    }

    public function import(Fund $fund, string $filePath): void
    {
        $data = $this->readKeyValuePairs($this->loadDataSetSheet($filePath));

        $this->mapDate($fund, $data);
        $this->mapPerformance($fund, $data);
        $this->mapAssetAllocation($fund, $data);
        $this->mapFixedIncomeStatistics($fund, $data);
        $this->mapMaturityBreakdown($fund, $data);
        $this->mapSectorExposure($fund, $data);
        $this->mapSynopsisAndStrategy($fund, $data, ['SA', 'southAfrica'], self::QR);
    }

    // ── Performance ──────────────────────────────────────────────────────

    /**
     * fund      = {code}_FOORD_{n}Y_TO_D
     * benchmark = {code}_FOORD_COMP_1_{n}Y_TO_D
     * peer      = {code}_{n}_YEAR_MORNINGSTAR_FUND_AVG_RETURN
     *
     * @param  array<string, mixed>  $data
     */
    private function mapPerformance(Fund $fund, array $data): void
    {
        $stored = $fund->performance_table ?? [];
        $storedFunds = collect($stored['groups'] ?? [])
            ->flatMap(fn (array $group): array => $group['funds'] ?? [])
            ->keyBy(fn (array $entry): string => (string) ($entry['code'] ?? ''));

        $groups = [];
        foreach (self::PERFORMANCE_GROUPS as $groupDef) {
            $funds = [];
            foreach ($groupDef['funds'] as $def) {
                $code = $def['code'];
                $prior = $storedFunds->get($code) ?? [];

                $funds[] = [
                    'code' => $code,
                    'name' => $prior['name'] ?? $def['name'],
                    'className' => $prior['className'] ?? $def['className'],
                    'peerLabel' => $prior['peerLabel'] ?? $def['peerLabel'],
                    'benchmarkLabel' => $prior['benchmarkLabel'] ?? $def['benchmarkLabel'],
                    'fund' => $this->performanceValues($data, fn (int $n) => "{$code}_FOORD_{$n}Y_TO_D"),
                    'peer' => $this->performanceValues($data, fn (int $n) => "{$code}_{$n}_YEAR_MORNINGSTAR_FUND_AVG_RETURN"),
                    'benchmark' => $this->performanceValues($data, fn (int $n) => "{$code}_FOORD_COMP_1_{$n}Y_TO_D"),
                ];
            }

            $groups[] = ['label' => $groupDef['label'], 'funds' => $funds];
        }

        $fund->performance_table = [
            'title' => $stored['title'] ?? 'PERFORMANCE %',
            'headers' => self::PERIOD_HEADERS,
            'columnKeys' => array_keys(self::PERIODS),
            'groups' => $groups,
            'footnotes' => $stored['footnotes'] ?? self::PERFORMANCE_FOOTNOTES,
        ];
    }

    // ── Asset allocation ─────────────────────────────────────────────────

    /**
     * {code}_AA_DOM_* / {code}_AA_FRGN_* / {code}_AA_TOTAL_EQ → one value
     * per fund column per row. A fund with no AA keys at all contributes
     * nothing to the value maps (absent, not zero).
     *
     * @param  array<string, mixed>  $data
     */
    private function mapAssetAllocation(Fund $fund, array $data): void
    {
        $stored = $fund->asset_allocation ?? [];
        $storedColumns = collect($stored['columns'] ?? [])->keyBy(fn (array $column): string => (string) ($column['code'] ?? ''));
        $storedRows = collect($stored['rows'] ?? [])->keyBy(fn (array $row): string => (string) ($row['key'] ?? ''));

        $columns = [];
        foreach (self::AA_COLUMNS as $def) {
            $columns[] = [
                'code' => $def['code'],
                'label' => $storedColumns->get($def['code'])['label'] ?? $def['label'],
            ];
        }

        $exportedCodes = [];
        foreach (self::AA_COLUMNS as $def) {
            foreach (array_keys($data) as $key) {
                if (str_starts_with((string) $key, "{$def['code']}_AA_")) {
                    $exportedCodes[] = $def['code'];
                    break;
                }
            }
        }

        $rows = [];
        foreach (self::AA_ROWS as $def) {
            $values = [];
            foreach ($exportedCodes as $code) {
                $value = $def['feed'] === null
                    ? $this->allocationTotal($data, $code)
                    : $this->allocationValue($data["{$code}_AA_{$def['feed']}"] ?? null);
                if ($value !== null) {
                    // Code-keyed: PHP turns '824' into int 824, so build the
                    // map by assignment rather than array_merge.
                    $values[$code] = $value;
                }
            }

            $row = [
                'key' => $def['key'],
                'label' => $storedRows->get($def['key'])['label'] ?? $def['label'],
            ];
            if (isset($def['style'])) {
                $row['style'] = $def['style'];
            }
            $row['values'] = $values;

            $rows[] = $row;
        }

        $fund->asset_allocation = [
            'title' => $stored['title'] ?? 'ASSET ALLOCATION %',
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function allocationTotal(array $data, string $code): ?float
    {
        $dom = $this->allocationValue($data["{$code}_AA_DOM_TOTAL"] ?? null);
        $frgn = $this->allocationValue($data["{$code}_AA_FRGN_TOTAL"] ?? null);

        if ($dom === null && $frgn === null) {
            return null;
        }

        return round(($dom ?? 0.0) + ($frgn ?? 0.0), 1);
    }

    // ── Fixed-income statistics ──────────────────────────────────────────

    /**
     * {code}_STAT_* for 825/824/826 and 826_BM_* for the ALBI column →
     * page2_content['fixedIncome']. Yield keeps the feed string ("9.44%");
     * durations print with two decimals. Unusable cells ("%", blank,
     * "ERR>>>…") keep the stored value so a defective month never blanks
     * the table.
     *
     * @param  array<string, mixed>  $data
     */
    private function mapFixedIncomeStatistics(Fund $fund, array $data): void
    {
        $page2 = $fund->page2_content ?? [];
        $stored = $page2['fixedIncome'] ?? [];
        $storedColumns = collect($stored['columns'] ?? [])->keyBy(fn (array $column): string => (string) ($column['code'] ?? ''));
        $storedRows = collect($stored['rows'] ?? [])->keyBy(fn (array $row): string => (string) ($row['key'] ?? ''));

        $columns = [];
        foreach (self::STAT_COLUMNS as $def) {
            $columns[] = [
                'code' => $def['code'],
                'label' => $storedColumns->get($def['code'])['label'] ?? $def['label'],
            ];
        }

        $rows = [];
        foreach (self::STAT_ROWS as $def) {
            $prior = $storedRows->get($def['key']) ?? [];
            $priorValues = $prior['values'] ?? [];
            $isYield = $def['key'] === 'yield';

            $values = [];
            foreach (self::STAT_COLUMNS as $column) {
                $code = $column['code'];
                if ($code === '826_BM') {
                    $feedKey = $def['albi'] === null ? null : "826_BM_{$def['albi']}";
                } else {
                    $feedKey = "{$code}_STAT_{$def['feed']}";
                }

                if ($feedKey === null) {
                    $values[$code] = '-';

                    continue;
                }

                $raw = $data[$feedKey] ?? null;
                $values[$code] = $this->usableStat($raw)
                    ? $this->formatStat((string) $raw, $isYield)
                    : (string) ($priorValues[$code] ?? '-');
            }

            $row = [
                'key' => $def['key'],
                'label' => $prior['label'] ?? $def['label'],
            ];
            if (isset($def['style'])) {
                $row['style'] = $def['style'];
            }
            $row['values'] = $values;

            $rows[] = $row;
        }

        $page2['fixedIncome'] = [
            'title' => $stored['title'] ?? 'FIXED INCOME FUNDS',
            'tableTitle' => $stored['tableTitle'] ?? 'PORTFOLIO STATISTICS',
            'columns' => $columns,
            'rows' => $rows,
        ];
        $fund->page2_content = $page2;
    }

    // ── Maturity breakdown ───────────────────────────────────────────────

    /**
     * 826_MATURITY_* / 826_MATURITY_BM_* → chart_data['maturityData'] in the
     * bond template's shape; other chart_data keys are untouched.
     *
     * @param  array<string, mixed>  $data
     */
    private function mapMaturityBreakdown(Fund $fund, array $data): void
    {
        if (! array_key_exists('826_MATURITY_0_TO_1_YEAR', $data)) {
            return;
        }

        $categories = [];
        foreach (self::MATURITY_BUCKETS as $suffix => $name) {
            $categories[] = [
                'name' => $name,
                'fund' => $this->chartNumber($data["826_MATURITY_{$suffix}"] ?? null),
                'benchmark' => $this->chartNumber($data["826_MATURITY_BM_{$suffix}"] ?? null),
            ];
        }

        $chartData = $fund->chart_data ?? [];
        $maturity = $chartData['maturityData'] ?? [];
        $maturity['title'] = $maturity['title'] ?? 'Foord Bond Fund Maturity Breakdown';
        $maturity['categories'] = $categories;
        $chartData['maturityData'] = $maturity;
        $fund->chart_data = $chartData;
    }

    // ── Sector exposure ──────────────────────────────────────────────────

    /**
     * 811_ESAOT_RANK_{n}_{ITEM,CURRENT,BENCHMARK} → sector_allocation. The
     * count is whatever the export carries (12 in August, 13 on the
     * reference); blank ITEM ranks are skipped.
     *
     * The July export names the same list without the 811_ prefix and
     * without BENCHMARK cells, so bare ESAOT_RANK_n keys are accepted too,
     * and a benchmark the feed omits entirely keeps the stored figure for
     * that sector name.
     *
     * @param  array<string, mixed>  $data
     */
    private function mapSectorExposure(Fund $fund, array $data): void
    {
        $prefix = match (true) {
            array_key_exists('811_ESAOT_RANK_1_ITEM', $data) => '811_ESAOT_RANK_',
            array_key_exists('ESAOT_RANK_1_ITEM', $data) => 'ESAOT_RANK_',
            default => null,
        };
        if ($prefix === null) {
            return;
        }

        $stored = $fund->sector_allocation ?? [];

        $fund->sector_allocation = [
            'title' => $stored['title'] ?? 'SECTOR EXPOSURE (FOORD EQUITY)',
            'sectors' => $this->mapSectorList($data, $prefix, $stored['sectors'] ?? []),
        ];
    }
}
