<?php

/**
 * Seed the Foord Shariah Income Fund (841) Class B.
 *
 *   php artisan tinker
 *   >>> include 'Funds/841 - Shariah Income Fund/seed-841-shariah-income.php';
 *
 * Re-runnable: matches on (fund_code, class_code) and overwrites the static
 * text only. Everything the monthly Excel feed owns — portfolio structure,
 * maturity spread, credit exposure, performance figures, TIC values, unit
 * price, distributions, chart series — is left to `php artisan fund:import`.
 *
 * Static text is transcribed from the signed-off reference in `Design/`:
 * "Foord Shariah Income Fund Class B at 2026-07-31.pdf".
 * See FUND-ONBOARDING.md §5p.
 */

use App\Models\Fund;

$fund = Fund::firstOrNew(['fund_code' => '841', 'class_code' => 'B']);

$fund->user_id = $fund->user_id ?? 1;
$fund->name = 'FOORD SHARIAH INCOME FUND — CLASS B';
$fund->template = 'show-shariah-income';
$fund->description = 'The fund aims to provide an income yield exceeding returns from money market portfolios with a low probability of capital loss over an investment horizon of six months to one year. The fund is suitable for South African Islamic investors seeking an attractive income yield and who have a very low tolerance for short-term capital loss or price volatility.';
// Without this the accessor yields '' rather than null, so the template's own
// fallback never fires and the <img> renders its "FOORD" alt text.
$fund->logo_url = 'https://foord.co.za/themes/custom/mirum/logo.png';

// ---------------------------------------------------------------- sidebar
$fund->domicile = 'South Africa';
$fund->management_company = 'Foord Unit Trusts (RF) (Pty) Ltd<br>VAT Registration Number: 4560201594';
$fund->fund_managers = 'Farzana Bayat and Rashaad Tayob';
$fund->inception_date = '6 May 2025';
$fund->base_currency = 'South African rands';
$fund->category = 'South African — Multi Asset — Income';
$fund->benchmark = 'Alexander Forbes Short-term Fixed-interest Composite Index (Stefi)';
$fund->minimums = 'By agreement';
// The published sheet carries the "INCOME DISIBUTIONS" typo; corrected here as
// on the other income-family sheets (§5f/§5h).
$fund->income_distributions = 'End-March, end-June, end-September and end-December each year.';
$fund->income_characteristics = 'High income yield, expected to exceed average money market yields.';
$fund->portfolio_orientation = 'A dynamic mix of Islamic deposits and sukuks and listed property counters, with some select foreign securities and active currency management. Weighted average duration is typically less than three years.';
$fund->significant_restrictions = 'Maximum equity exposure of 10%; maximum property exposure of 25%; maximum offshore exposure of 45%; complies with pension fund investment regulations (Regulation 28). Shariah compliant, determined by the Shariah Advisory Committee.';
$fund->foreign_assets = 'Direct investment in global hard-currency securities, with active currency management.';
$fund->risk_of_loss = 'Low in periods longer than one year, moderate in periods shorter than six months.';
$fund->time_horizon = 'One to two years.';
$fund->isin_number = 'ZAE000338331';
// No equity indicator on the income-family sheets.
$fund->equity_indicator_description = null;

// ------------------------------------------------------- page-1 block titles
$assetAllocation = $fund->asset_allocation ?? [];
$assetAllocation['title'] = 'PORTFOLIO STRUCTURE %';
$stats = $assetAllocation['portfolioStatistics'] ?? [];
$stats['title'] = 'PORTFOLIO STATISTICS';
// The 2026-07 feed exports a bare "%" for STAT_YIELD and STAT_SPREAD_TO_JIBAR
// and blanks for every duration, so these are seeded from the published July
// reference and preserved by the importer until the feed is fixed. They stay
// inline-editable on the fund page.
$stats['rows'] = [
    ['name' => 'Yield', 'sup' => '1', 'value' => '8.60%'],
    ['name' => 'Spread to JIBAR', 'value' => '1.79%'],
    ['spacer' => true],
    ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'value' => '5.01'],
    ['name' => '— SA fixed rate duration', 'value' => '5.01'],
    ['name' => '— SA floating rate duration', 'value' => '-'],
    ['name' => '— SA inflation linked duration', 'value' => '-'],
    ['name' => 'Offshore duration', 'sup' => '2', 'bold' => true, 'value' => '-'],
    ['name' => '— Offshore fixed rate duration', 'value' => '-'],
    ['name' => '— Offshore inflation linked', 'value' => '-'],
];
$assetAllocation['portfolioStatistics'] = $stats;
$credit = $assetAllocation['creditExposure'] ?? [];
$credit['title'] = 'CREDIT EXPOSURE BREAKDOWN %';
$assetAllocation['creditExposure'] = $credit;
$fund->asset_allocation = $assetAllocation;

$chartData = $fund->chart_data ?? [];
$spread = $chartData['maturitySpread'] ?? [];
$spread['title'] = 'MATURITY SPREAD %';
$chartData['maturitySpread'] = $spread;
$chartData['rightTitle'] = 'PERFORMANCE VS BENCHMARK';
$chartData['portfolioLabels'] = ['Fund', 'Benchmark'];
$fund->chart_data = $chartData;

// -------------------------------------------------------- performance table
// EIGHT columns; only the Fund and Benchmark rows print (no highest/lowest).
// YTD must be a bare <br> and never "&nbsp;<br>" — the header→row-key map
// strips tags and collapses /\s+/, which does not match a non-breaking space,
// so an &nbsp; there silently blanks the whole column.
$performance = $fund->performance_table ?? [];
$performance['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED⁵)';
$performance['headers'] = ['', 'CASH<br>VALUE⁴', 'SINCE<br>INCEPTION', '1<br>YEAR', '6<br>MONTHS', '3<br>MONTHS', '<br>YTD', 'THIS<br>MONTH'];
// Footnotes 1–6 + the rounding note print at the BOTTOM OF PAGE 1 on this
// reference. Transcribed verbatim, including the double space in "The yield  is".
$performance['footnotes'] = [
    '¹ The yield for an income-generating investment is its annual income distribution divided by its current price, expressed as a percentage. For the Fund, the yield represents the weighted average yield of the underlying income-generating investments as at the last day of the month. The yield  is subject to change as market conditions and the composition of the underlying investments change.',
    '² Duration measures the sensitivity of the price of an income-generating instrument to changes in prevailing market profit rates or yields. A lower duration indicates lower sensitivity to changes in market conditions, while a higher duration indicates greater sensitivity.',
    '³ Average credit rating from rating agencies.',
    '⁴ Current value of R100 000 notional lump sum invested at inception, distributions reinvested (graphically represented in R’000s above)',
    '⁵ Converted to reflect the average yearly return for each period presented.',
    '⁶ Net of fees and expenses.',
    'Note: Totals may not cast perfectly due to rounding.',
];
$fund->performance_table = $performance;

// ----------------------------------------------------------------- page 2
$fund->important_info_title = 'IMPORTANT INFORMATION FOR INVESTORS';
$fund->important_info_paragraphs = [
    'Foord Unit Trusts (RF) (Pty) Ltd (Foord) is an approved CISCA Management Company (#10), regulated by the Financial Sector Conduct Authority. Portfolios are managed by Foord Asset Management (Pty) Ltd, an authorised Financial Services Provider (FSP: 578). The custodian/trustee of Foord Unit Trusts is RMB Custody and Trustee Services (a division of FirstRand Bank Limited), contactable on T: 087 736 1732,F: 0860 557 774, www.rmb.co.za.',
    'Collective Investment Schemes in Securities (unit trusts) are generally medium- to long-term investments. The value of participatory interests (units) may go down as well as up and past performance is not necessarily a guide to the future. Performance is calculated for the portfolio. Individual investor performance may differ as a result of the actual investment date, the date of reinvestment and withholding taxes. Performance may be affected by changes in the market or economic conditions and legal, regulatory and tax requirements. Foord does not provide any guarantee either with respect to the capital or the performance return of the investment. Unit trusts are traded at ruling prices and can engage in borrowing. Foord does not engage in scrip lending. Commission and incentives may be paid and if so, this cost is not borne by the investor. A schedule of fees and charges and maximum commissions is available on request. Distributions may be subject to mandatory withholding taxes. Portfolio information is presented using effective exposures. A fund of funds invests only in other Collective Investment Scheme portfolios, which may levy their own charges, which could result in a higher fee structure. A feeder fund is a portfolio that, apart from assets in liquid form, consists solely of units in a single portfolio of a Collective Investment Scheme which could result in a higher fee structure. Foord is authorised to close the portfolio to new investors in order to manage the portfolio more efficiently in accordance with its mandate.',
    'Unit trust prices are calculated on a net asset value basis, which is the total value of all assets in the portfolio including any income accruals and less any permissible deductions from the portfolio. Forward pricing is used. Prices are determined at 15h00 each business day and are published daily on www.foord.co.za. The cut-off time for instruction is 14h00 each business day.',
    'The portfolio may include underlying foreign investments. Fluctuations or movements in exchange rates may cause the value of underlying foreign investments to go up or down. The underlying foreign investments may be adversely affected by political instability as well as exchange controls, changes in taxation, foreign investment policies, restrictions on repatriation of investments and other restrictions and controls that may be imposed by the relevant authorities in the relevant countries.',
    'This document is not an advertisement, but is provided exclusively for information purposes and is not an offer or solicitation to purchase, sell or otherwise deal with any particular investment. Economic forecasts and predictions are based on Foord’s interpretation of current factual information and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur. While we have taken and will continue to take care that the information contained herein is true and correct, we request that you report any errors to Foord at unittrusts@foord.co.za. The document is protected by copyright and may not be altered without prior written consent.',
    'Foord Asset Management is a member of the Association for Savings and Investment SA.',
    'This is a Minimum Disclosure Document.',
];
$fund->footer_info = 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.';
$fund->footer_free_of_charge = 'This information is provided free of charge.';
$fund->footer_phone = '+27 21 532 6969';
$fund->footer_email = 'unittrusts@foord.co.za';
$fund->footer_website = 'www.foord.co.za';

// The FOORD SHARIAH FUNDS block, shared verbatim with the 840 sheets.
// "investor's" carries a straight apostrophe in the reference.
$page2 = $fund->page2_content ?? [];
$page2['shariahFunds'] = [
    'title' => 'FOORD SHARIAH FUNDS',
    'paragraphs' => [
        'Foord Shariah compliant funds are managed according to the rulings and guidelines issued by the Shariah Advisory Committee (SAC). The term Shariah refers to Islamic Law as interpreted by the Shariah Advisory Committee appointed by Foord. The SAC is an independent body of scholars, specialist in Islamic commercial law and are entrusted with the duty of directing, reviewing, and supervising the activities of the Foord Shariah compliant funds to ensure that these funds are always Shariah compliant.',
        'Non-permissible income (NPI) refers to non-Shariah compliant income, primarily being interest or resulting from involvement in prohibited activities. Such NPI may inadvertently be earned by the Foord Shariah compliant fund portfolios. Any  income earned in the Foord Shariah compliant funds deemed non-permissible by the SAC will be paid to the charitable organisations approved by the SAC in accordance with the principles of Shariah. Such NPI does not form part of the investor\'s income nor will such NPI be re-invested within the Foord Shariah compliant funds.',
    ],
];
$fund->page2_content = $page2;

// -------------------------------------------------------------------- fees
// No PERFORMANCE FEES or PERFORMANCE FEE EXAMPLES sections on this sheet —
// the templates render those blocks only when the keys are present.
$fees = $fund->fees ?? [];
unset($fees['performanceFees'], $fees['performanceFeeExamples']);
$fees['feeRates'] = [
    'title' => 'FEE RATES',
    'rates' => [
        ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
        ['name' => 'Manager’s charge', 'value' => 'Zero fee class. Fee rates are by agreement only'],
    ],
];
$tic = $fees['totalInvestmentCharge'] ?? [];
$tic['title'] = 'TOTAL INVESTMENT CHARGE %';
// The star sits on the 36-month heading, not on the TER row.
$tic['headers'] = ['', '12 MONTHS', '36 MONTHS*'];
$tic['footnote'] = '*Estimated as the fund was incepted less than three  years ago. The manager has applied the best estimate of the costs annualised for a year which is grounded on fair principles of ASISA’s standard on the Calculation and Disclosure of Total Expense Ratios and Transaction costs.';
$tic['description'] = 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average daily value of the portfolio. These expenses include the annual fee, VAT, audit fees, bank charges and costs (excluding trading costs) incurred in any underlying funds. Included in the TER, but separately disclosed, is a performance fee (or credit) resulting from overperformance (or underperformance) against the benchmark. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. Performance return information and prices are always stated net of the expenses, fees and charges included in the TER.';
$fees['totalInvestmentCharge'] = $tic;
$fund->fees = $fees;

$fund->save();

echo ($fund->wasRecentlyCreated ? 'created' : 'updated')." fund {$fund->id} — {$fund->name}\n";
