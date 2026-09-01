<?php

// Seed script — Foord Inflation Linked Income Fund Class B2 (827).
// Statics transcribed from the published reference:
// "Funds/827 - Inflation Linked Income Fund/Foord Inflation Linked Income
//  Fund Class B2 at 2026-06-30.pdf".
// Disclaimer paragraphs / footer / logo cloned from fund 33 (825 B2), which
// matches the 827 reference verbatim (including the "Additional detailed
// analysis…" paragraph). Run once via tinker; re-runnable (updates in place).

use App\Models\Fund;

$source = Fund::findOrFail(33);

$fund = Fund::where('fund_code', '827')->where('class_code', 'B2')->first();
if (! $fund) {
    $fund = new Fund;
    $fund->user_id = $source->user_id;
}

$fund->name = 'FOORD INFLATION LINKED INCOME FUND — CLASS B2';
$fund->fund_code = '827';
$fund->class_code = 'B2';
$fund->template = 'show-inflation-income';
$fund->fund_date = '30 June 2026';
$fund->description = 'The portfolio shall invest in a broad spectrum of securities, the allocation of which will be appropriate for South African retirement funds and investors seeking high levels of inflation beating income with some prospects of capital gain and low tolerance for capital loss over an investment horizon of 12 months to 2 years.';
$fund->logo_url = $source->logo_url;

// Sidebar statics (reference page 1)
$fund->domicile = $source->domicile;                      // South Africa
$fund->management_company = $source->management_company;  // Foord Unit Trusts (RF) (Pty) Ltd + VAT no
$fund->fund_managers = 'Farzana Bayat and Rashaad Tayob';
$fund->inception_date = '18 November 2024';
$fund->base_currency = $source->base_currency;            // South African rands
$fund->category = 'South African — Multi Asset — Income';
$fund->benchmark = 'SA Headline CPI';
$fund->minimums = 'R20 000 / R1 000';
$fund->income_distributions = 'End-March, end-June, end-September and end-December each year.';
$fund->income_characteristics = 'Inflation-beating income yield.';
$fund->portfolio_orientation = 'The portfolio shall comprise a mix of permissible inflation-linked and fixed incomes securities including foreign securities.';
$fund->significant_restrictions = 'Maximum equity exposure of 10%; maximum property exposure of 25%; maximum offshore exposure of 45%; complies with pension fund investment regulations (Regulation 28).';
$fund->foreign_assets = 'Direct investment in global non-equity hard-currency securities, with active currency management.';
$fund->risk_of_loss = 'Low in periods longer than one year, moderate in periods shorter than six months.';
$fund->time_horizon = 'One to two years.';
$fund->equity_indicator_description = null;               // no equity indicator dots

// PORTFOLIO STRUCTURE % — the published table lists ILB maturity buckets the
// feed does not carry; rows (incl. the reference's duplicated "Replica ILB
// 2—3 years" line) are stored values maintained by hand via inline edit.
$fund->asset_allocation = [
    'title' => 'PORTFOLIO STRUCTURE %',
    'subtitle' => 'Change since 31 March 2026',
    'headers' => ['', 'TOTAL', 'CHANGE'],
    'rows' => [
        ['name' => 'Money market',          'value' => '4.5',  'change' => '▼ 0.2', 'changeDirection' => 'down'],
        ['name' => 'RSA ILB 2—3 years',     'value' => '19.1', 'change' => '▼ 0.7', 'changeDirection' => 'down'],
        ['name' => 'Replica ILB 2—3 years', 'value' => '3.1',  'change' => '▼ 0.1', 'changeDirection' => 'down'],
        ['name' => 'Corp ILB 2—3 years',    'value' => '12.3', 'change' => '▼ 0.6', 'changeDirection' => 'down'],
        ['name' => 'RSA ILB 3—4 years',     'value' => '46.5', 'change' => '▼ 1.2', 'changeDirection' => 'down'],
        ['name' => 'Replica ILB 2—3 years', 'value' => '3.2',  'change' => '▼ 0.1', 'changeDirection' => 'down'],
        ['name' => 'Replica ILB 3—5 years', 'value' => '3.1',  'change' => '▲ 3.1', 'changeDirection' => 'up'],
        ['name' => 'RSA ILB 4—8 years',     'value' => '8.2',  'change' => '▼ 0.2', 'changeDirection' => 'down'],
    ],
    'total' => ['name' => 'TOTAL', 'value' => '100.0', 'change' => ''],
    // Seeded from the June reference; the feed exports ERR for both keys, so
    // these are preserved on import and inline-editable on the page.
    'portfolioStatistics' => [
        'title' => 'PORTFOLIO STATISTICS',
        'rows' => [
            ['name' => 'Real Yield', 'sup' => '1', 'value' => '4.01%'],
            ['name' => 'Duration', 'sup' => '2', 'value' => '2.54'],
        ],
    ],
];

// Performance table statics — rows come from the import.
$fund->performance_table = [
    'title' => 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED⁵)',
    'headers' => ['', 'CASH<br>VALUE⁴', 'SINCE<br>INCEPTION', '1<br>YEAR', '6<br>MONTHS', '3<br>MONTHS', 'YTD', 'THIS<br>MONTH'],
    // Footnotes 1–6 print at the bottom of page 1 (⁴ has no trailing full
    // stop, per the reference).
    'footnotes' => [
        '¹ The yield for an interest bearing security is it’s annual income divided by it’s current price expressed as a percentage. For the fund, the yield is a weighted average yield of all underlying interest bearing securities as at the last day of the month. It is subject to change as market rates and underlying investments change.',
        '² Duration is the measure of the sensitivity of the price of the instrument to a change in interest rates, with a smaller number indicating less sensitivity and a larger number indicating more sensitivity.',
        '³ Average credit rating from rating agencies.',
        '⁴ Current value of R100 000 notional lump sum invested at inception, distributions reinvested (graphically represented in R’000s above)',
        '⁵ Converted to reflect the average yearly return for each period presented.',
        '⁶ Net of fees and expenses.',
        'Note: Totals may not cast perfectly due to rounding.',
    ],
];

// Fees — TIC rows/total come from the import; the starred 36 MONTHS* header
// pairs with the "*Estimated…" footnote (fund incepted November 2024).
$fund->fees = [
    'feeRates' => [
        'title' => 'FEE RATES',
        'rates' => [
            ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
            ['name' => 'Manager’s charge', 'value' => '0.4 plus VAT'],
        ],
    ],
    'totalInvestmentCharge' => [
        'title' => 'TOTAL INVESTMENT CHARGE %',
        'headers' => ['', '12 MONTHS', '36 MONTHS*'],
        'footnote' => '*Estimated as the fund was incepted less than three years ago. The manager has applied the best estimate of the costs annualised for three years which is grounded on fair principles of ASISA’s standard on the Calculation and Disclosure of Total Expense Ratios and Transaction costs.',
        'description' => 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average daily value of the portfolio. These expenses include the annual fee, VAT, audit fees, bank charges and costs (excluding trading costs) incurred in any underlying funds. Included in the TER, but separately disclosed, is a performance fee (or credit) resulting from overperformance (or underperformance) against the benchmark. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. Performance return information and prices are always stated net of the expenses, fees and charges included in the TER.',
    ],
];

// Footer + important information — cloned from fund 33 (verbatim match with
// the 827 reference, including "Additional detailed analysis…").
$fund->footer_info = $source->footer_info;
$fund->footer_email = $source->footer_email;
$fund->footer_phone = $source->footer_phone;
$fund->footer_website = $source->footer_website;
$fund->footer_logo_url = $source->footer_logo_url;
$fund->footer_free_of_charge = $source->footer_free_of_charge;
$fund->important_info_title = $source->important_info_title;
$fund->important_info_paragraphs = $source->important_info_paragraphs;
$fund->important_info_published_date = 'Published on 03 July 2026.';

$fund->save();

echo "Seeded fund {$fund->id} — {$fund->name}\n";
