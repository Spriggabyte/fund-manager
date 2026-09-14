<?php

/**
 * Seed the "FUND OVERVIEW: SOUTH AFRICA" sheet — one class-less record with
 * fund code LOC, rendered by show-local-overview.blade.php.
 *
 * Statics transcribed from the July 2026 Publisher reference
 * ("Local Fund Overview - July 2026.pdf" in this folder). Everything numeric
 * (performance grid, asset allocation, fixed-income statistics, maturity and
 * sector charts, synopsis/strategy bullets, month-end date) refreshes from the
 * monthly LOCAL_OVERVIEW.xlsx export via fund:import — this file seeds only
 * the prose the export omits: banner description, group labels, the peer
 * group / benchmark row labels, footnotes, the QR block and the footer.
 * LocalOverviewImporter merges these back by fund code / row key on every
 * import, so a re-seed never wipes imported figures and an import never
 * wipes seeded prose.
 *
 * Run (idempotent, re-runnable):
 *   php artisan tinker --execute='include "Funds/Fund overview - local/seed-local-overview.php";'
 * Then:
 *   php artisan fund:import <id> storage/app/private/fund-data/2026-07/LOC
 */

use App\Models\Fund;

$userId = Fund::where('fund_code', '810')->value('user_id') ?? Fund::query()->value('user_id');

$fund = Fund::firstOrNew(['fund_code' => 'LOC', 'class_code' => null]);

$fund->fill([
    'name' => 'FUND OVERVIEW: SOUTH AFRICA',
    'class' => 'Overview',
    'template' => 'show-local-overview',
    'fund_date' => $fund->fund_date ?? '31 July 2026',
    'description' => 'Foord is an owner-managed boutique built on the principles of investment stewardship. A multi-decade track record of successful investing evidences our capacity to deliver superior investment returns for a range of investment strategies.',
    'logo_url' => 'https://foord.co.za/themes/custom/mirum/logo.png',
    'footer_info' => 'Please refer to the fact sheets carried on www.foord.co.za for more detailed information.',
    'footer_phone' => '+27 21 532 6969',
    'footer_email' => 'unittrusts@foord.co.za',
    'footer_website' => 'www.foord.co.za',
]);
$fund->user_id = $fund->user_id ?? $userId;

// ------------------------------------------------ performance grid prose —
// Group labels and the per-fund row labels. Values (fund/peer/benchmark maps)
// are importer-owned and preserved here via array_replace by code.
$labels = [
    '818' => ['name' => 'Foord Conservative', 'className' => 'Class B2', 'peerLabel' => 'Peer group: South Africa — Multi Asset — Medium Equity', 'benchmarkLabel' => 'Benchmark: CPI + 4% per annum'],
    '820' => ['name' => 'Foord Domestic Balanced', 'className' => 'Class B2', 'peerLabel' => 'Peer group: South Africa — Multi Asset — SA High Equity', 'benchmarkLabel' => 'Benchmark: Average peer group excluding Foord'],
    '810' => ['name' => 'Foord Balanced', 'className' => 'Class B2', 'peerLabel' => 'Peer group: South Africa — Multi Asset — High Equity', 'benchmarkLabel' => 'Benchmark: MV weighted peer group excluding Foord'],
    '817' => ['name' => 'Foord Flexible', 'className' => 'Class B2', 'peerLabel' => 'Peer group: Worldwide Multi Asset — Flexible', 'benchmarkLabel' => 'Benchmark: CPI + 5% per annum'],
    '811' => ['name' => 'Foord Equity', 'className' => 'Class B2', 'peerLabel' => 'Peer group: South Africa — Equity — General (SA only)', 'benchmarkLabel' => 'Benchmark: FTSE / JSE Capped All Share index'],
];
$groupDefs = [
    ['label' => 'REGULATION 28', 'codes' => ['818', '820', '810']],
    ['label' => 'BEST INVESTMENT VIEW', 'codes' => ['817']],
    ['label' => 'SPECIALIST EQUITY', 'codes' => ['811']],
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
$performance = array_replace($performance, [
    'title' => 'PERFORMANCE %',
    'headers' => ['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'],
    'columnKeys' => ['20yrs', '15yrs', '10yrs', '5yrs', '3yrs', '1yr'],
    'groups' => $groups,
    'footnotes' => [
        'Source: Foord, Stats SA, Morningstar (Peer group: provisional)',
        'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
    ],
]);
$fund->performance_table = $performance;

// ------------------------------------------------------- page-2 prose —
$page2 = $fund->page2_content ?? [];
$page2['roundingNote'] = 'Note: Totals may not cast perfectly due to rounding';
$page2['qr'] = array_replace($page2['qr'] ?? [], [
    'heading' => 'SCAN QR CODE',
    'text' => 'to read regulatory disclosures or visit:',
    'url' => 'https://foord.co.za/terms-conditions-sa',
    'image' => 'images/qr-terms-sa.png',
]);
$fund->page2_content = $page2;

$fund->save();

echo "Seeded fund {$fund->id} — {$fund->name}\n";
