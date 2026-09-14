<?php

/**
 * Seed the "FUND OVERVIEW: GLOBAL" sheet — one class-less record with fund
 * code GLB, rendered by show-global-overview.blade.php.
 *
 * Statics transcribed from the July 2026 Publisher reference
 * ("Global Fund Overview - July 2026.pdf" in this folder). The monthly
 * GLOBAL_OVERVIEW.xlsx export refreshes performance, asset allocation, the
 * synopsis/strategy bullets and the month-end date via fund:import. Two
 * blocks are seeded WITH numbers because the export currently carries only
 * their labels: the three geographic pies and the Foord Global Equity sector
 * bars. GlobalOverviewImporter leaves those blocks untouched until an export
 * carries *_CURRENT values, at which point the feed takes over — so the
 * figures below are the July 2026 reference numbers, not live data.
 *
 * Run (idempotent, re-runnable):
 *   php artisan tinker --execute='include "Funds/Fund overview - global/seed-global-overview.php";'
 * Then:
 *   php artisan fund:import <id> storage/app/private/fund-data/2026-07/GLB
 */

use App\Models\Fund;

$userId = Fund::where('fund_code', 'LOC')->value('user_id') ?? Fund::query()->value('user_id');

$fund = Fund::firstOrNew(['fund_code' => 'GLB', 'class_code' => null]);

$fund->fill([
    'name' => 'FUND OVERVIEW: GLOBAL',
    'class' => 'Overview',
    'template' => 'show-global-overview',
    'fund_date' => $fund->fund_date ?? '31 July 2026',
    'description' => 'Foord is an owner-managed boutique built on the principles of investment stewardship. A multi-decade track record of successful investing evidences our capacity to deliver superior investment returns for a range of investment strategies.',
    'logo_url' => 'https://foord.co.za/themes/custom/mirum/logo.png',
    'footer_info' => 'Please refer to the fact sheets carried on www.foord.com and www.foord.co.za for more detailed information.',
    'footer_phone' => '+27 21 532 6969',
    'footer_email' => 'unittrusts@foord.co.za',
    'footer_website' => 'www.foord.co.za',
]);
$fund->user_id = $fund->user_id ?? $userId;

// ------------------------------------------------ performance grid prose —
$labels = [
    '875' => ['name' => 'Foord International', 'className' => 'Class R in USD', 'peerLabel' => 'Peer group: Morningstar (USD Flexible Allocation)', 'benchmarkLabel' => 'Benchmark: US Inflation', 'feederNote' => '(SA Feeder Fund: Prescient Foord International Feeder Fund)'],
    '877' => ['name' => 'Foord Global Equity', 'className' => 'Class R1 in USD', 'peerLabel' => 'Peer group: Morningstar (Global Large-Cap Blend Equity)', 'benchmarkLabel' => 'Benchmark: MSCI All Country World Net Total Return', 'feederNote' => '(SA Feeder Fund: Prescient Foord Global Equity Feeder Fund)'],
    '879' => ['name' => 'Foord Asia ex-Japan', 'className' => 'Class R in USD', 'peerLabel' => 'Peer group: Morningstar (Asia ex-Japan Equity)', 'benchmarkLabel' => 'Benchmark: MSCI Asia ex-Japan USD'],
];
$groupDefs = [
    ['label' => 'BEST INVESTMENT VIEW', 'codes' => ['875']],
    ['label' => 'SPECIALIST EQUITY', 'codes' => ['877', '879']],
];

$performance = $fund->performance_table ?? [];
$existingFunds = [];
foreach ($performance['groups'] ?? [] as $group) {
    foreach ($group['funds'] ?? [] as $entry) {
        $existingFunds[(string) ($entry['code'] ?? '')] = $entry;
    }
}
$groups = [];
foreach ($groupDefs as $def) {
    $funds = [];
    foreach ($def['codes'] as $code) {
        $funds[] = array_replace($existingFunds[$code] ?? [], ['code' => $code], $labels[$code]);
    }
    $groups[] = ['label' => $def['label'], 'funds' => $funds];
}
$fund->performance_table = array_replace($performance, [
    'title' => 'PERFORMANCE %',
    'headers' => ['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'],
    'columnKeys' => ['20yrs', '15yrs', '10yrs', '5yrs', '3yrs', '1yr'],
    'groups' => $groups,
    'footnotes' => [
        'Source: Foord, Morningstar (Peer group: provisional).',
        'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
        'US headline consumer price index. Source: Bloomberg L.P. (lagged by one month).',
    ],
]);

// --------------------------------------- geographic pies (reference figures) —
// Slice order = the feed's rank order (positional palette). Values are the
// July 2026 reference percentages; the feed names "Africa + Middle East"
// and "India" where the reference printed "Africa & Middle East" /
// "Hong Kong" — the feed's own labels win once it carries values.
$chartData = $fund->chart_data ?? [];
$geo = $chartData['geographicExposure'] ?? [];
$storedPies = collect($geo['funds'] ?? [])->keyBy(fn ($f) => (string) ($f['code'] ?? ''));
$pieDefs = [
    ['code' => '875', 'label' => 'FOORD INTERNATIONAL FUND', 'footnote' => '1', 'slices' => [
        ['name' => 'North America', 'value' => 38.4],
        ['name' => 'Europe', 'value' => 29.6],
        ['name' => 'EM Asia', 'value' => 20.9],
        ['name' => 'Pacific', 'value' => 10.3],
        ['name' => 'Africa & Middle East', 'value' => 0.8],
        ['name' => 'EM Latin America', 'value' => 0.0],
    ]],
    ['code' => '877', 'label' => 'FOORD GLOBAL EQUITY FUND', 'footnote' => '2', 'slices' => [
        ['name' => 'EM Asia', 'value' => 32.9],
        ['name' => 'North America', 'value' => 31.1],
        ['name' => 'Europe', 'value' => 29.6],
        ['name' => 'Pacific', 'value' => 6.4],
    ]],
    ['code' => '879', 'label' => 'FOORD ASIA EX-JAPAN FUND', 'footnote' => '2', 'slices' => [
        ['name' => 'China', 'value' => 47.9],
        ['name' => 'Taiwan, Province of China', 'value' => 10.5],
        ['name' => 'Korea', 'value' => 9.4],
        ['name' => 'United States of America', 'value' => 7.2],
        ['name' => 'Singapore', 'value' => 5.8],
        ['name' => 'Hong Kong', 'value' => 4.9],
        ['name' => 'Other', 'value' => 14.3],
    ]],
];
$pies = [];
foreach ($pieDefs as $def) {
    $prior = $storedPies->get($def['code']) ?? [];
    // Keep imported slices if the feed has ever supplied them; otherwise seed.
    $pies[] = array_replace($def, ['slices' => $prior['slices'] ?? $def['slices']]);
}
$chartData['geographicExposure'] = array_replace($geo, [
    'title' => 'GEOGRAPHIC EXPOSURE',
    'funds' => $pies,
    'footnotes' => [
        ['marker' => '1', 'text' => 'Gross exposure'],
        ['marker' => '2', 'text' => 'Equity only exposure'],
    ],
]);
$fund->chart_data = $chartData;

// --------------------------------------- sector exposure (reference figures) —
// Measured off the reference bars at 200dpi (1.34mm per percentage point);
// replaced wholesale once the feed carries 877_ESAOT_RANK_n_CURRENT/BENCHMARK.
$sectorAllocation = $fund->sector_allocation ?? [];
if (empty($sectorAllocation['sectors'])) {
    $sectorAllocation['sectors'] = [
        ['name' => 'Communication services', 'fund' => 19.6, 'benchmark' => 7.7],
        ['name' => 'Consumer discretionary', 'fund' => 14.8, 'benchmark' => 8.4],
        ['name' => 'Information technology', 'fund' => 12.7, 'benchmark' => 27.4],
        ['name' => 'Energy', 'fund' => 9.5, 'benchmark' => 15.9],
        ['name' => 'Financials', 'fund' => 9.4, 'benchmark' => 3.4],
        ['name' => 'Healthcare', 'fund' => 7.4, 'benchmark' => 8.1],
        ['name' => 'Industrials', 'fund' => 6.9, 'benchmark' => 10.8],
        ['name' => 'Consumer staples', 'fund' => 6.4, 'benchmark' => 4.4],
        ['name' => 'Cash', 'fund' => 5.7, 'benchmark' => 0],
        ['name' => 'Materials', 'fund' => 3.4, 'benchmark' => 3.4],
        ['name' => 'Utilities', 'fund' => 1.7, 'benchmark' => 2.2],
        ['name' => 'Real estate', 'fund' => 0.3, 'benchmark' => 1.4],
    ];
}
$sectorAllocation['title'] = 'SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)';
$fund->sector_allocation = $sectorAllocation;

// ------------------------------------------------------- page-2 prose —
$page2 = $fund->page2_content ?? [];
$page2['roundingNote'] = 'Note: Totals may not cast perfectly due to rounding';
$page2['qr'] = array_replace($page2['qr'] ?? [], [
    'heading' => 'SCAN QR CODE',
    'text' => 'to read regulatory disclosures or visit:',
    'url' => 'https://foord.com/terms-conditions',
    'image' => 'images/qr-terms-global.png',
]);
$fund->page2_content = $page2;

$fund->save();

echo "Seeded fund {$fund->id} — {$fund->name}\n";
