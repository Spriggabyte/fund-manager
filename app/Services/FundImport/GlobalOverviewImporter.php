<?php

namespace App\Services\FundImport;

use App\Models\Fund;

/**
 * GLOBAL_OVERVIEW.xlsx → the FUND OVERVIEW: GLOBAL record.
 *
 * One export covers three funds' performance and asset allocation (875
 * Foord International, 877 Foord Global Equity, 879 Foord Asia ex-Japan),
 * three geographic-exposure pies, the 877 sector chart and the quarterly
 * world / Asia synopsis and strategy bullets. Keys are prefixed with the
 * fund code except the bullets and MONTH_END_DATE.
 *
 * Two things differ from the local sheet. The comparison roles are
 * reversed: COMP_1 is the peer group and COMP_2 the benchmark (the local
 * sheet's peer came from MORNINGSTAR keys and COMP_1 was the benchmark).
 * And the feed does not yet carry chart figures — the pies and the sector
 * list arrive as names only — so a fund's stored slices/sectors (seeded
 * from the reference) are left untouched until the export carries at least
 * one _CURRENT value for that chart, at which point the list is rebuilt
 * from the feed.
 *
 * As with the local sheet, the importer owns every table skeleton and
 * merges prose from the stored record by code/key; missing numbers are
 * absent keys, never placeholder strings.
 */
class GlobalOverviewImporter extends AbstractOverviewImporter
{
    /**
     * Performance grid skeleton. `feederNote` is a fourth, prose-only row
     * naming the SA feeder fund; 879 has none and the key is omitted.
     *
     * @var list<array{label: string, funds: list<array{code: string, name: string, className: string, peerLabel: string, benchmarkLabel: string, feederNote?: string}>}>
     */
    private const PERFORMANCE_GROUPS = [
        ['label' => 'BEST INVESTMENT VIEW', 'funds' => [
            ['code' => '875', 'name' => 'Foord International', 'className' => 'Class R in USD',
                'peerLabel' => 'Peer group: Morningstar (USD Flexible Allocation)',
                'benchmarkLabel' => 'Benchmark: US Inflation',
                'feederNote' => '(SA Feeder Fund: Prescient Foord International Feeder Fund)'],
        ]],
        ['label' => 'SPECIALIST EQUITY', 'funds' => [
            ['code' => '877', 'name' => 'Foord Global Equity', 'className' => 'Class R1 in USD',
                'peerLabel' => 'Peer group: Morningstar (Global Large-Cap Blend Equity)',
                'benchmarkLabel' => 'Benchmark: MSCI All Country World Net Total Return',
                'feederNote' => '(SA Feeder Fund: Prescient Foord Global Equity Feeder Fund)'],
            ['code' => '879', 'name' => 'Foord Asia ex-Japan', 'className' => 'Class R in USD',
                'peerLabel' => 'Peer group: Morningstar (Asia ex-Japan Equity)',
                'benchmarkLabel' => 'Benchmark: MSCI Asia ex-Japan USD'],
        ]],
    ];

    /** @var list<string> */
    private const PERFORMANCE_FOOTNOTES = [
        'Source: Foord, Morningstar (Peer group: provisional).',
        'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
        'US headline consumer price index. Source: Bloomberg L.P. (lagged by one month).',
    ];

    /** @var list<array{code: string, label: string}> */
    private const AA_COLUMNS = [
        ['code' => '875', 'label' => 'FOORD<br>INTERNATIONAL'],
        ['code' => '877', 'label' => 'FOORD<br>GLOBAL EQUITY'],
        ['code' => '879', 'label' => 'FOORD<br>ASIA EX-JAPAN'],
    ];

    /**
     * Asset-allocation rows in print order. `feed` is the {code}_AA_TOTAL_
     * suffix. HEQ has been blank in every export so far and the reference
     * prints 0.0, so a blank cell reads as zero for any fund that exported
     * allocation keys at all (`blankAsZero`). TOTAL is the sum of the
     * seven feed rows.
     *
     * @var list<array{key: string, label: string, feed: string, style?: string, blankAsZero?: bool}>
     */
    private const AA_ROWS = [
        ['key' => 'netEq', 'label' => 'Net equities', 'feed' => 'EQ'],
        ['key' => 'hedgedEq', 'label' => 'Hedged equities', 'feed' => 'HEQ', 'style' => 'muted', 'blankAsZero' => true],
        ['key' => 'prop', 'label' => 'Property', 'feed' => 'PROP'],
        ['key' => 'debt', 'label' => 'Corporate bonds', 'feed' => 'DEBT'],
        ['key' => 'bond', 'label' => 'Government bonds', 'feed' => 'BOND'],
        ['key' => 'comm', 'label' => 'Commodities', 'feed' => 'COMM'],
        ['key' => 'cash', 'label' => 'Money market', 'feed' => 'CASH'],
    ];

    /** @var array{key: string, label: string, style: string} */
    private const AA_TOTAL_ROW = ['key' => 'total', 'label' => 'TOTAL', 'style' => 'total'];

    /**
     * Geographic-exposure pies in print order. `prefix` is the rank-name
     * key stem: `{prefix}{n}` is the region/country name and
     * `{prefix}{n}_CURRENT` its (not yet exported) percentage.
     *
     * @var list<array{code: string, label: string, footnote: string, prefix: string}>
     */
    private const GEO_FUNDS = [
        ['code' => '875', 'label' => 'FOORD INTERNATIONAL FUND', 'footnote' => '1', 'prefix' => '875_GEO_EXP_TOTAL_RANK_'],
        ['code' => '877', 'label' => 'FOORD GLOBAL EQUITY FUND', 'footnote' => '2', 'prefix' => '877_GEO_EXP_TOTAL_RANK_'],
        ['code' => '879', 'label' => 'FOORD ASIA EX-JAPAN FUND', 'footnote' => '2', 'prefix' => '879_COUNTRY_EXP_RANK_'],
    ];

    /** @var list<array{marker: string, text: string}> */
    private const GEO_FOOTNOTES = [
        ['marker' => '1', 'text' => 'Gross exposure'],
        ['marker' => '2', 'text' => 'Equity only exposure'],
    ];

    private const SECTOR_PREFIX = '877_ESAOT_RANK_';

    /** @var array{heading: string, text: string, url: string, image: string} */
    private const QR = [
        'heading' => 'SCAN QR CODE',
        'text' => 'to read regulatory disclosures or visit:',
        'url' => 'https://foord.com/terms-conditions',
        'image' => 'images/qr-terms-global.png',
    ];

    public function supports(string $filename): bool
    {
        return str_starts_with(strtoupper(basename($filename)), 'GLOBAL_OVERVIEW');
    }

    public function label(): string
    {
        return 'global fund overview';
    }

    public function import(Fund $fund, string $filePath): void
    {
        $data = $this->readKeyValuePairs($this->loadDataSetSheet($filePath));

        $this->mapDate($fund, $data);
        $this->mapPerformance($fund, $data);
        $this->mapAssetAllocation($fund, $data);
        $this->mapGeographicExposure($fund, $data);
        $this->mapSectorExposure($fund, $data);
        $this->mapSynopsisAndStrategy($fund, $data, ['ASIA', 'asia'], self::QR);
    }

    // ── Performance ──────────────────────────────────────────────────────

    /**
     * fund      = {code}_FOORD_{n}Y_TO_D
     * peer      = {code}_FOORD_COMP_1_{n}Y_TO_D
     * benchmark = {code}_FOORD_COMP_2_{n}Y_TO_D
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

                $entry = [
                    'code' => $code,
                    'name' => $prior['name'] ?? $def['name'],
                    'className' => $prior['className'] ?? $def['className'],
                    'peerLabel' => $prior['peerLabel'] ?? $def['peerLabel'],
                    'benchmarkLabel' => $prior['benchmarkLabel'] ?? $def['benchmarkLabel'],
                ];
                $feederNote = $prior['feederNote'] ?? $def['feederNote'] ?? null;
                if ($feederNote !== null) {
                    $entry['feederNote'] = $feederNote;
                }
                $entry['fund'] = $this->performanceValues($data, fn (int $n) => "{$code}_FOORD_{$n}Y_TO_D");
                $entry['peer'] = $this->performanceValues($data, fn (int $n) => "{$code}_FOORD_COMP_1_{$n}Y_TO_D");
                $entry['benchmark'] = $this->performanceValues($data, fn (int $n) => "{$code}_FOORD_COMP_2_{$n}Y_TO_D");

                $funds[] = $entry;
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
     * {code}_AA_TOTAL_* → one value per fund column per row, plus a
     * computed TOTAL. A fund with no AA keys at all contributes nothing to
     * the value maps (absent, not zero).
     *
     * @param  array<string, mixed>  $data
     */
    private function mapAssetAllocation(Fund $fund, array $data): void
    {
        $stored = $fund->asset_allocation ?? [];
        $storedColumns = collect($stored['columns'] ?? [])->keyBy(fn (array $column): string => (string) ($column['code'] ?? ''));
        $storedRows = collect($stored['rows'] ?? [])->keyBy(fn (array $row): string => (string) ($row['key'] ?? ''));

        $columns = [];
        $exportedCodes = [];
        foreach (self::AA_COLUMNS as $def) {
            $columns[] = [
                'code' => $def['code'],
                'label' => $storedColumns->get($def['code'])['label'] ?? $def['label'],
            ];

            foreach (array_keys($data) as $key) {
                if (str_starts_with((string) $key, "{$def['code']}_AA_")) {
                    $exportedCodes[] = $def['code'];
                    break;
                }
            }
        }

        $totals = [];
        $rows = [];
        foreach (self::AA_ROWS as $def) {
            $values = [];
            foreach ($exportedCodes as $code) {
                $value = $this->allocationValue($data["{$code}_AA_TOTAL_{$def['feed']}"] ?? null);
                if ($value === null && ($def['blankAsZero'] ?? false)) {
                    $value = 0.0;
                }
                if ($value !== null) {
                    // Code-keyed: PHP turns '875' into int 875, so build the
                    // map by assignment rather than array_merge.
                    $values[$code] = $value;
                    $totals[$code] = ($totals[$code] ?? 0.0) + $value;
                }
            }

            $rows[] = $this->allocationRow($def, $storedRows->get($def['key'])['label'] ?? null, $values);
        }

        $totalValues = [];
        foreach ($exportedCodes as $code) {
            if (array_key_exists($code, $totals)) {
                $totalValues[$code] = round($totals[$code], 1);
            }
        }
        $rows[] = $this->allocationRow(self::AA_TOTAL_ROW, $storedRows->get('total')['label'] ?? null, $totalValues);

        $fund->asset_allocation = [
            'title' => $stored['title'] ?? 'ASSET ALLOCATION %',
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    /**
     * @param  array{key: string, label: string, style?: string}  $def
     * @param  array<int|string, float>  $values
     * @return array<string, mixed>
     */
    private function allocationRow(array $def, ?string $storedLabel, array $values): array
    {
        $row = [
            'key' => $def['key'],
            'label' => $storedLabel ?? $def['label'],
        ];
        if (isset($def['style'])) {
            $row['style'] = $def['style'];
        }
        $row['values'] = $values;

        return $row;
    }

    // ── Geographic exposure ──────────────────────────────────────────────

    /**
     * Three pies → chart_data['geographicExposure']. Region/country names
     * come from {prefix}{n} (blank rank ends the list) and values from
     * {prefix}{n}_CURRENT. A fund whose export carries at least one
     * _CURRENT value has its slices rebuilt from the feed ("-"/blank → 0);
     * otherwise its stored slices are kept untouched. Title, labels and
     * footnotes merge from the stored block; other chart_data keys are
     * untouched.
     *
     * @param  array<string, mixed>  $data
     */
    private function mapGeographicExposure(Fund $fund, array $data): void
    {
        $chartData = $fund->chart_data ?? [];
        $stored = $chartData['geographicExposure'] ?? [];
        $storedFunds = collect($stored['funds'] ?? [])->keyBy(fn (array $entry): string => (string) ($entry['code'] ?? ''));

        $funds = [];
        foreach (self::GEO_FUNDS as $def) {
            $prior = $storedFunds->get($def['code']) ?? [];

            $funds[] = [
                'code' => $def['code'],
                'label' => $prior['label'] ?? $def['label'],
                'footnote' => $prior['footnote'] ?? $def['footnote'],
                'slices' => $this->hasRankValues($data, $def['prefix'], '_CURRENT')
                    ? $this->slices($data, $def['prefix'])
                    : ($prior['slices'] ?? []),
            ];
        }

        $chartData['geographicExposure'] = [
            'title' => $stored['title'] ?? 'GEOGRAPHIC EXPOSURE',
            'funds' => $funds,
            'footnotes' => $stored['footnotes'] ?? self::GEO_FOOTNOTES,
        ];
        $fund->chart_data = $chartData;
    }

    /**
     * Whether any rank of a list carries the given value cell
     * ({prefix}{n}{suffix}) — the signal that the feed has started
     * exporting figures for that chart.
     *
     * @param  array<string, mixed>  $data
     */
    private function hasRankValues(array $data, string $prefix, string $suffix): bool
    {
        for ($n = 1; $n <= self::MAX_RANKS; $n++) {
            if (array_key_exists("{$prefix}{$n}{$suffix}", $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{name: string, value: float}>
     */
    private function slices(array $data, string $prefix): array
    {
        $slices = [];
        for ($n = 1; $n <= self::MAX_RANKS; $n++) {
            $name = trim((string) ($data["{$prefix}{$n}"] ?? ''));
            if ($name === '') {
                break;
            }

            $slices[] = [
                'name' => $name,
                'value' => round($this->numeric($data["{$prefix}{$n}_CURRENT"] ?? null) ?? 0.0, 1),
            ];
        }

        return $slices;
    }

    // ── Sector exposure ──────────────────────────────────────────────────

    /**
     * 877_ESAOT_RANK_{n}_{ITEM,CURRENT,BENCHMARK} → sector_allocation, with
     * the same rule as the pies: rebuilt only once the export carries at
     * least one CURRENT cell, else the stored list is kept untouched.
     *
     * @param  array<string, mixed>  $data
     */
    private function mapSectorExposure(Fund $fund, array $data): void
    {
        if (! array_key_exists(self::SECTOR_PREFIX.'1_ITEM', $data)) {
            return;
        }

        $stored = $fund->sector_allocation ?? [];
        $storedSectors = $stored['sectors'] ?? [];

        $fund->sector_allocation = [
            'title' => $stored['title'] ?? 'SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)',
            'sectors' => $this->hasRankValues($data, self::SECTOR_PREFIX, '_CURRENT')
                ? $this->mapSectorList($data, self::SECTOR_PREFIX, $storedSectors)
                : $storedSectors,
        ];
    }

    /**
     * The 877 sheet exports "Property" where the reference prints
     * "Real estate".
     */
    protected function sectorName(string $raw): string
    {
        $name = parent::sectorName($raw);

        return $name === 'Property' ? 'Real estate' : $name;
    }
}
