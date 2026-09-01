<?php

/**
 * Seed the Foord Shariah Balanced Fund (840) Classes B and B3.
 *
 *   php artisan tinker
 *   >>> include 'Funds/840 - Shariah Balanced Fund/seed-840-shariah.php';
 *
 * Re-runnable: matches on (fund_code, class_code) and overwrites the static
 * text only. Everything the monthly Excel feed owns — asset allocation, top 10,
 * performance figures, TIC values, unit price, distributions, chart series —
 * is left to `php artisan fund:import`.
 *
 * Static text is transcribed from the signed-off references in `Design/`:
 * "Foord Shariah Balanced Fund Class B at 2026-07-31.pdf" and
 * "…Class B3 at 2026-06-30.pdf". See FUND-ONBOARDING.md §5n.
 */

use App\Models\Fund;

$description = 'The fund aims to grow retirement savings by meaningful, inflation-beating returns over the long term from an actively managed multi-asset class portfolio of Shariah-permissible securities. The fund is appropriate for South African Islamic Investors for pre-retirement build up or post-retirement draw down, seeking long-term, inflation-beating returns, from a retirement fund investment product with a moderate investment risk profile.';

// Page-2 important-information column. Identical on both classes; the closing
// TER sentence is refreshed per class by the factsheet importer.
$importantInfo = [
    'Foord Unit Trusts (RF) (Pty) Ltd (Foord) is an approved CISCA Management Company (#10), regulated by the Financial Sector Conduct Authority. Portfolios are managed by Foord Asset Management (Pty) Ltd, an authorised Financial Services Provider (FSP: 578). The custodian/trustee of Foord Unit Trusts is RMB Custody and Trustee Services (a division of FirstRand Bank Limited), contactable on T: 0877361732, www.rmb.co.za.',
    // "can engage in Shariah-compliant borrowing" is the Shariah sheets' wording
    // — the Foord-branded balanced sheet reads "can engage in borrowing".
    'Collective Investment Schemes in Securities (unit trusts) are generally medium- to long-term investments. The value of participatory interests (units) may go down as well as up and past performance is not necessarily a guide to the future. Performance is calculated for the portfolio. Individual investor performance may differ as a result of the actual investment date, the date of reinvestment and withholding taxes. Performance may be affected by changes in the market or economic conditions and legal, regulatory and tax requirements. Foord does not provide any guarantee either with respect to the capital or the performance return of the investment. Unit trusts are traded at ruling prices and can engage in Shariah-compliant borrowing. Foord does not engage in scrip lending. Commission and incentives may be paid and if so, this cost is not borne by the investor. A schedule of fees and charges and maximum commissions is available on request. Distributions may be subject to mandatory withholding taxes. Portfolio information is presented using effective exposures. A fund of funds invests only in other Collective Investment Scheme portfolios, which may levy their own charges, which could result in a higher fee structure. A feeder fund is a portfolio that, apart from assets in liquid form, consists solely of units in a single portfolio of a Collective Investment Scheme which could result in a higher fee structure. Foord is authorised to close the portfolio to new investors in order to manage the portfolio more efficiently in accordance with its mandate.',
    'Unit trust prices are calculated on a net asset value basis, which is the total value of all assets in the portfolio including any income accruals and less any permissible deductions from the portfolio. Forward pricing is used. Prices are determined at 15h00 each business day and are published daily on www.foord.co.za. The cut-off time for instruction is 14h00 each business day.',
    'The portfolio may include underlying foreign investments. Fluctuations or movements in exchange rates may cause the value of underlying foreign investments to go up or down. The underlying foreign investments may be adversely affected by political instability as well as exchange controls, changes in taxation, foreign investment policies, restrictions on repatriation of investments and other restrictions and controls that may be imposed by the relevant authorities in the relevant countries.',
    'This document is not an advertisement, but is provided exclusively for information purposes and is not an offer or solicitation to purchase, sell or otherwise deal with any particular investment. Economic forecasts and predictions are based on Foord’s interpretation of current factual information and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur. While we have taken and will continue to take care that the information contained herein is true and correct, we request that you report any errors to Foord at unittrusts@foord.co.za. The document is protected by copyright and may not be altered without prior written consent. Foord Asset Management is a member of the Association for Savings and Investment SA.',
    'This is a Minimum Disclosure Document. Additional detailed analysis is published in the Quarterly Portfolio Report available on www.foord.co.za.',
];

// The FOORD SHARIAH FUNDS block, unique to the Shariah sheets and identical on
// both classes. "investor's" carries a straight apostrophe in both references.
$shariahFunds = [
    'title' => 'FOORD SHARIAH FUNDS',
    'paragraphs' => [
        'Foord Shariah compliant funds are managed according to the rulings and guidelines issued by the Shariah Advisory Committee (SAC). The term Shariah refers to Islamic Law as interpreted by the Shariah Advisory Committee appointed by Foord. The SAC is an independent body of scholars, specialist in Islamic commercial law and are entrusted with the duty of directing, reviewing, and supervising the activities of the Foord Shariah compliant funds to ensure that these funds are always Shariah compliant.',
        'Non-permissible income (NPI) refers to non-Shariah compliant income, primarily being interest or resulting from involvement in prohibited activities. Such NPI may inadvertently be earned by the Foord Shariah compliant fund portfolios. Any income earned in the Foord Shariah compliant funds deemed non-permissible by the SAC will be paid to the charitable organisations approved by the SAC in accordance with the principles of Shariah. Such NPI does not form part of the investor\'s income nor will such NPI be re-invested within the Foord Shariah compliant funds.',
    ],
];

$terDescription = 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average daily value of the portfolio. These expenses include the annual fee, VAT, audit fees, bank charges and costs (excluding trading costs) incurred in any underlying funds. Included in the TER, but separately disclosed, is a performance fee (or credit) resulting from overperformance (or underperformance) against the benchmark. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. Performance return information and prices are always stated net of the expenses, fees and charges included in the TER.';

// Stored with the superscript marker baked into each string, and the rounding
// note as a trailing unnumbered entry — the shape fund 9 uses.
$performanceFootnotes = [
    '¹ Converted to reflect the average yearly return for each period presented',
    '² Current value of R100 000 notional lump sum invested at inception, distributions reinvested (graphically represented in R’000s above)',
    '³ Net of fees and expenses',
    '⁴ Source: Morningstar, performance as calculated by Foord',
    'Note: Totals may not cast perfectly due to rounding',
];

// Seven columns, no highest/lowest rolling rows — the 840 sheets list only
// Fund and Benchmark. The superscripts are decorations on the stored names;
// the importer matches on the plain names.
// YTD prints on the lower line alone in both references; the leading break
// keeps it there against its two-line neighbours (th is vertically centred).
// It must be a bare <br> — the template maps headers to row keys by stripping
// tags and collapsing /\s+/, which does not match a non-breaking space, so an
// &nbsp; here silently blanks the whole YTD column.
$performanceHeaders = ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '1<br>YR', '6<br>MONTHS', '3<br>MONTHS', '<br>YTD', 'THIS<br>MONTH'];

$classes = [
    'B' => [
        'inception_date' => '1 November 2024',
        'isin_number' => 'ZAE000338315',
        'feeRates' => [
            ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
            ['name' => 'Manager’s charge', 'value' => 'Zero fee class. Fee rates are by agreement only'],
        ],
        // Class B was incepted in November 2024, so the 36-month column is an
        // estimate annualised over three years.
        'ticFootnote' => '*Estimated as the fund was incepted less than three years ago. The manager has applied the best estimate of the costs annualised for three years which is grounded on fair principles of ASISA’s standard on the Calculation and Disclosure of Total Expense Ratios and Transaction costs.',
    ],
    'B3' => [
        'inception_date' => '19 September 2024',
        'isin_number' => 'ZAE000338349',
        'feeRates' => [
            ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
            ['name' => 'Manager’s charge', 'value' => '(0.55% - x) plus VAT, where x is the underlying market value of Foord-Hassen Shariah Global Equity Fund shares in the portfolio'],
            ['name' => 'Foreign assets', 'value' => '0.50% fixed annual fee plus 15% performance fee is charged in Foord-Hassen Shariah Global Equity Fund'],
        ],
        // Reference quirk kept verbatim: the B3 sheet annualises "for a year"
        // where Class B reads "for three years", against the same 36-month
        // column heading.
        'ticFootnote' => '*Estimated as the fund was incepted less than three years ago. The manager has applied the best estimate of the costs annualised for a year which is grounded on fair principles of ASISA’s standard on the Calculation and Disclosure of Total Expense Ratios and Transaction costs.',
    ],
];

foreach ($classes as $classCode => $spec) {
    $fund = Fund::firstOrNew(['fund_code' => '840', 'class_code' => $classCode]);

    $fund->user_id = $fund->user_id ?? 1;
    $fund->name = 'FOORD SHARIAH BALANCED FUND — CLASS '.$classCode;
    $fund->template = 'show-shariah';
    $fund->description = $description;
    // Without this the accessor yields '' rather than null, so the template's
    // own fallback never fires and the <img> renders its "FOORD" alt text.
    $fund->logo_url = 'https://foord.co.za/themes/custom/mirum/logo.png';

    // Sidebar
    $fund->domicile = 'South Africa';
    $fund->management_company = 'Foord Unit Trusts (RF) (Pty) Ltd<br>VAT Registration Number: 4560201594';
    $fund->fund_managers = 'Rashaad Tayob and Farzana Bayat';
    $fund->inception_date = $spec['inception_date'];
    $fund->base_currency = 'South African rands';
    $fund->equity_indicator_description = 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.';
    $fund->category = 'South African — Multi Asset — High Equity';
    $fund->benchmark = 'CPI + 4% per annum';
    $fund->minimums = 'By agreement';
    $fund->income_distributions = 'End-March and end-September each year.';
    $fund->income_characteristics = 'Moderate income yield. Income distributions are reduced by the annual service charge, which varies with the relative performance of the fund against its benchmark.';
    $fund->portfolio_orientation = 'The portfolio comprises a mix of securities including sukuks, Islamic-compliant instruments, foreign securities and assets in liquid form.';
    $fund->significant_restrictions = 'Maximum equity exposure of 75%; maximum offshore exposure of 45%; complies with pension fund investment regulations (Regulation 28). Shariah compliant, determined by the Shariah Advisory Committee.';
    $fund->foreign_assets = 'Principally via the Shariah-compliant US dollar-denominated Foord global fund domiciled in Luxembourg, plus select additional individual securities suitable to the fund’s risk mandate.';
    $fund->risk_of_loss = 'Low in periods longer than three years, high in periods shorter than one year.';
    $fund->time_horizon = 'Longer than three years.';
    $fund->isin_number = $spec['isin_number'];

    // Page 2
    $fund->important_info_title = 'IMPORTANT INFORMATION FOR INVESTORS';
    $fund->important_info_paragraphs = $importantInfo;
    $fund->footer_info = 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.';
    $fund->footer_free_of_charge = 'This information is provided free of charge.';
    $fund->footer_phone = '+27 21 532 6969';
    $fund->footer_email = 'unittrusts@foord.co.za';
    $fund->footer_website = 'www.foord.co.za';

    $page2 = $fund->page2_content ?? [];
    $page2['shariahFunds'] = $shariahFunds;
    $fund->page2_content = $page2;

    // Fees. No PERFORMANCE FEES or PERFORMANCE FEE EXAMPLES sections on the
    // Shariah sheets — both templates render those blocks only when present,
    // so the keys are cleared rather than seeded.
    $fees = $fund->fees ?? [];
    unset($fees['performanceFees'], $fees['performanceFeeExamples']);
    $fees['feeRates'] = [
        'title' => 'FEE RATES',
        'rates' => $spec['feeRates'],
    ];
    $tic = $fees['totalInvestmentCharge'] ?? [];
    $tic['title'] = 'TOTAL INVESTMENT CHARGE %';
    // The star sits on the 36-month heading, not on the TER row.
    $tic['headers'] = ['', '12 MONTHS', '36 MONTHS*'];
    $tic['footnote'] = $spec['ticFootnote'];
    $tic['description'] = $terDescription;
    $fees['totalInvestmentCharge'] = $tic;
    $fund->fees = $fees;

    // Performance table shell — the importer fills rows and cash values.
    $performance = $fund->performance_table ?? [];
    $performance['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)';
    $performance['headers'] = $performanceHeaders;
    $performance['footnotes'] = $performanceFootnotes;
    $fund->performance_table = $performance;

    $fund->save();

    echo ($fund->wasRecentlyCreated ? 'created' : 'updated')." fund {$fund->id} — {$fund->name}\n";
}
