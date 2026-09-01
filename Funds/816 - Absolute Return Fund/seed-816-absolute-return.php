<?php

/**
 * Seed the Foord Absolute Return Fund — 816 Class A.
 *
 * Layout is the signed-off balanced template (fund 9 / 810 Class A) with the
 * 816 reference's graph presentation — see FUND-ONBOARDING.md §5l and
 * resources/views/funds/{show,pdf}-absolute.blade.php.
 *
 * Statics transcribed from the published reference
 * ("Foord Absolute Return Fund Class A at 2026-06-30.pdf", now in Design/).
 * Every table, chart and scalar refreshes monthly via fund:import.
 *
 * Run (idempotent, re-runnable):
 *   php artisan tinker --execute='include "Funds/816 - Absolute Return Fund/seed-816-absolute-return.php";'
 */

use App\Models\Fund;

$balanced = Fund::where('fund_code', '810')->where('class_code', 'A')->first();
$userId = $balanced?->user_id ?? Fund::query()->value('user_id');

$fund = Fund::firstOrNew(['fund_code' => '816', 'class_code' => 'A']);

// user_id is guarded, so it is set outside fill().
$fund->user_id = $fund->user_id ?? $userId;

$fund->fill([
    'name' => 'FOORD ABSOLUTE RETURN FUND — CLASS A',
    'template' => 'show-absolute',
    'logo_url' => 'https://foord.co.za/themes/custom/mirum/logo.png',
    'footer_logo_url' => $balanced?->footer_logo_url,

    'description' => 'The fund aims to provide investors with a return of inflation plus 5% per annum, measured over rolling three-year periods, while exploiting the benefits of diversification and reflecting Foord’s prevailing best investment view on all available asset classes. It caters to institutional investors with a moderate risk profile who require long-term inflation beating total returns from a dynamically managed multi-asset class portfolio of South African securities.',

    // ------------------------------------------------------------- sidebar —
    'domicile' => 'South Africa',
    'management_company' => 'Foord Unit Trusts (RF) (Pty) Ltd<br>VAT Registration Number: 4560201594',
    'fund_managers' => 'Dave Foord',
    'inception_date' => '1 April 2008',
    'base_currency' => 'South African rands',
    'equity_indicator_description' => 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.',
    // The 816 sheet prints plain hyphens here (the balanced sheet uses em dashes).
    'category' => 'South African - Multi Asset - Flexible',
    'benchmark' => 'CPI + 5% per annum, which is applied daily using the most recently available inflation data and accordingly will be lagged on average by 5 to 6 weeks.',
    // Rendered under the "NEW INVESTMENTS" heading on this template.
    'minimums' => 'At the manager’s discretion',
    'income_distributions' => 'End-March and end-September each year.',
    'income_characteristics' => 'Low to medium income yield depending on the asset allocation strategy employed.',
    'portfolio_orientation' => 'Exploiting the benefits of diversification, the portfolio continually reflects Foord’s prevailing best investment view on all available asset classes.',
    'significant_restrictions' => 'None. The fund is unconstrained.',
    // The reference has no FOREIGN ASSETS section.
    'foreign_assets' => null,
    'risk_of_loss' => 'Lower than that of a pure equity fund. High in periods shorter than six months, lower in periods greater than one year.',
    'time_horizon' => 'Longer than three years.',
    'isin_number' => 'ZAE000116513',

    // -------------------------------------------------------------- footer —
    'footer_info' => 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.',
    'footer_free_of_charge' => 'This information is provided free of charge.',
    'footer_phone' => '+27 21 532 6969',
    'footer_email' => 'unittrusts@foord.co.za',
    'footer_website' => 'www.foord.co.za',

    // ------------------------------------------------------ important info —
    'important_info_title' => 'IMPORTANT INFORMATION FOR INVESTORS',
    'important_info_paragraphs' => $balanced?->important_info_paragraphs,
]);

// Scalars below are refreshed by fund:import; seed them so the record renders
// before the first import.
$fund->fund_date = $fund->fund_date ?: '30 June 2026';
$fund->portfolio_size = $fund->portfolio_size ?: 'R3.7 billion';
$fund->unit_price = $fund->unit_price ?: '3600.37 cents';
$fund->number_of_units = $fund->number_of_units ?: '4 067';
$fund->last_distributions = $fund->last_distributions ?: '31/03/2026: 10.90 cents<br>30/09/2025: 45.80 cents';
$fund->important_info_published_date = $fund->important_info_published_date ?: 'Published on 28 July 2026.';

// ------------------------------------------------- block titles / headers —
$assetAllocation = $fund->asset_allocation ?? [];
$assetAllocation['title'] = 'ASSET ALLOCATION %';
$fund->asset_allocation = $assetAllocation;

$sectorAllocation = $fund->sector_allocation ?? [];
$sectorAllocation['title'] = 'EQUITY SECTOR ALLOCATION %';
$fund->sector_allocation = $sectorAllocation;

$topInvestments = $fund->top_investments ?? [];
$topInvestments['title'] = 'TOP 10 INVESTMENTS';
$topInvestments['headers'] = ['SECURITY', 'ASSET CLASS', 'MARKET', '% OF FUND'];
$fund->top_investments = $topInvestments;

// -------------------------------------------------------- performance table —
$performanceTable = $fund->performance_table ?? [];
$performanceTable['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)';
$performanceTable['headers'] = [
    '',
    'CASH<br>VALUE²',
    'SINCE<br>INCEPTION',
    '15<br>YRS',
    '10<br>YRS',
    '7<br>YRS',
    '5<br>YRS',
    '3<br>YRS',
    '1<br>YR',
    'THIS<br>MONTH',
];
$performanceTable['footnotes'] = [
    '¹ Converted to reflect the average yearly return for each period presented',
    '² Current value of R100 000 notional lump sum invested at inception, distributions reinvested (graphically represented in R’000s above)',
    '³ Net of fees and expenses',
    '⁴ Source: Stats SA, performance as calculated by Foord',
    '⁵ Source: IRESS MD RSA',
    '⁶ Highest and lowest actual 12 month rand return achieved in the period',
    'Note: Totals may not cast perfectly due to rounding.',
];
$fund->performance_table = $performanceTable;

// ----------------------------------------------------------------- fees —
$fees = $fund->fees ?? [];

$fees['feeRates'] = [
    // The 816 sheet names the class in the heading; there are no Foord global
    // fund sub-rows (the fund holds South African securities directly).
    'title' => 'FEE RATES (CLASS A)',
    'rates' => [
        ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
        ['name' => 'Standard annual fee for equalling benchmark', 'value' => '1.0% plus VAT'],
        ['name' => 'Performance fee sharing rate', 'value' => '10% (over– and under-performance)'],
        ['name' => 'Minimum annual fee', 'value' => '0.5% plus VAT'],
        ['name' => 'Maximum annual fee', 'value' => 'Uncapped'],
    ],
    'description' => 'The annual fee is based on portfolio performance with the daily rate being adjusted up or down based on the portfolio’s one-year rolling return relative to that of its benchmark and is subject to a minimum fee rate.',
];

$fees['performanceFees'] = [
    'title' => 'PERFORMANCE FEES',
    'paragraphs' => [
        // Reference wording differs from the balanced sheet by one hyphen:
        // "over- or underperformance".
        'Performance fees align investor and manager return objectives by rewarding the manager for outperformance while penalising the manager for underperformance. Foord’s performance fee structure increases or decreases the daily fee levied based on the over- or underperformance of the Foord unit trust portfolios relative to their benchmarks. When the portfolio return exceeds the benchmark return, the daily performance fee rate is increased proportionately. Similarly, underperformance causes the daily performance fee rate to decrease proportionately. Performance fee rates are not capped because outperformance is generally not earned smoothly.',
        'The annual fee is adjusted up or down daily by the performance fee calculated as the difference between the rolling one-year net-of-fee return and the benchmark return for the same period, multiplied by the performance fee sharing rate.',
    ],
];

$tic = $fees['totalInvestmentCharge'] ?? [];
$tic['title'] = 'TOTAL INVESTMENT CHARGE %';
$tic['headers'] = ['', '12 MONTHS', '36 MONTHS'];
$tic['description'] = 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average daily value of the portfolio. These expenses include the annual fee, VAT, audit fees, bank charges and costs (excluding trading costs) incurred in any underlying funds. Included in the TER, but separately disclosed, is a performance fee (or credit) resulting from overperformance (or underperformance) against the benchmark. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. Performance return information and prices are always stated net of the expenses, fees and charges included in the TER. The TER for the fund’s financial year ended 31 March 2026 was 2.42%.';
$fees['totalInvestmentCharge'] = $tic;

$fees['performanceFeeExamples'] = [
    'title' => 'PERFORMANCE FEE EXAMPLES FOR THE FOORD ABSOLUTE RETURN FUND %',
    'headers' => ['', 'A', 'B', 'C', 'D'],
    'rows' => [
        ['name' => 'Foord 1-year rolling return', 'a' => 10, 'b' => 10, 'c' => 10, 'd' => 10],
        ['name' => 'Benchmark 1-year rolling return', 'a' => 8, 'b' => 12, 'c' => 10, 'd' => 16],
        ['name' => 'Relative performance', 'a' => '+2.0', 'b' => -2, 'c' => 0, 'd' => -6],
        ['name' => 'Performance fee sharing rate', 'a' => 10, 'b' => 10, 'c' => 10, 'd' => 10],
        ['name' => 'Adjustment to 1% annual fee', 'a' => '+0.2', 'b' => -0.2, 'c' => 0, 'd' => -0.6],
    ],
    'total' => ['name' => 'Annual fee rate applied (excl. VAT)', 'a' => 1.2, 'b' => 0.8, 'c' => 1, 'd' => '0.5*'],
    'footnote' => '* Minimum fees apply',
];

$fund->fees = $fees;
$fund->save();

echo "Seeded fund {$fund->id}: {$fund->name} (template {$fund->template})\n";
