<?php

/**
 * Seed the Foord-Hassen Shariah Global Equity Fund — 878 Class R.
 *
 * Statics transcribed from the signed-off Class R reference
 * ("Design/Foord-Hassen Shariah Global Equity Fund Class R at 2026-07-31
 * (reference).pdf"). The sheet is the Luxembourg sub-fund layout shared with
 * 877 (see Funds/877 …/seed-877-global-equity.php); the differences are the
 * Shariah supervisory board, the single fund manager, the Islamic benchmark
 * and Morningstar category, the SRRI 4 rating with the Shariah-criteria
 * sentence, and the "-hyphenated" footnote 5 wording.
 *
 * Every scalar the feed carries (ISIN, share price, number of shares,
 * portfolio size, TER, tables, chart series) refreshes from the monthly
 * exports via fund:import — this file seeds only what the exports omit.
 *
 * Run (idempotent, re-runnable):
 *   php artisan tinker --execute='include "Funds/878 - Hassen Shariah Global Equity Fund/seed-878-hassen-shariah.php";'
 */

use App\Models\Fund;

$userId = Fund::where('fund_code', '877')->value('user_id') ?? Fund::query()->value('user_id');

// ---------------------------------------------------------------- statics —
$description = 'The fund aims to achieve long-term capital growth from an actively managed and diversified Shariah-compliant portfolio of developed and emerging market equities and to thereby outperform its MSCI All Country World Islamic Index benchmark, without assuming greater risk. The fund is appropriate for investors with a long investment horizon and who can withstand bouts of investment volatility in the short to medium term.';

$importantInfoParagraphs = [
    'This is a marketing communication. Investors should read the prospectus and key information documents (“PRIIP KID”) — available in English at www.foord.com — and seek professional advice or consider investment suitability before investing in the fund. This document is not an advertisement but is provided for information purposes and should not be regarded as an offer or solicitation to purchase, sell or otherwise deal in the fund.',
    'Foord does not guarantee the capital invested or the performance of the investment. The portfolio includes qualifying investments listed on regulated exchanges outside the fund\'s domicile that carry risks as described in the prospectus, including the possibility of non-recoverable withholding taxes and non-repatriation of funds. Investment values and some costs may fluctuate because of factors including but not limited to currency exchange rates that can be affected by a wide range of economic factors.',
    'Economic forecasts and predictions are based on Foord’s interpretation of current factual information, and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur.',
    'While we have taken and will continue to take care that the information contained herein is true and correct, we do not guarantee the accuracy, timeliness or completeness of the information provided, and therefore disclaim any liability, damage (whether direct or consequential) or expense suffered as a result of reliance on the information.',
    'The document is protected by copyright and may not be altered without Foord’s consent.',
    'Note: For South African investors, this document is a Minimum Disclosure Document.',
    // The TER percentage is refreshed in place by the factsheet importer.
    'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average value of the portfolio. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. The latest audited TER is 0.01%. The quantum of transaction costs is affected by the quantum of the gross in and outflows over the period presented. A schedule of fees and charges is disclosed in the prospectus or PRIIP KID, which are available on request.',
];

// Notes 1 ("annualised periods great than") and 5 ("rand return") keep the
// reference's verbatim quirks; note 5 is hyphenated here where 877 is not.
$footnotes = [
    '<sup>1</sup> Returns in USD unless otherwise stated and annualised periods great than one year, meaning they are converted to reflect the average yearly return for each period presented.',
    '<sup>2</sup> Current value of 100 000 notional currency units invested at inception (graphically represented in $’000s above) .',
    '<sup>3</sup> Performance, net of fees and expenses, is calculated for the portfolio on a single pricing basis (i.e. NAV to NAV rolling monthly basis). Individual investor performance may differ as a result of the actual investment date. Past performance of the fund is not indicative of its future performance.',
    '<sup>4</sup> Islamic Global Equity (provisional). Source: Morningstar.',
    '<sup>5</sup> Highest and lowest actual 12-month rand return achieved in the period.',
    '<sup>6</sup> Source: Factset and internal.',
    'The portfolio information is presented using effective exposure.',
    'Totals may not cast perfectly due to rounding.',
];

$performanceFees = [
    'title' => 'PERFORMANCE FEES',
    'paragraphs' => [
        'Performance fees align investor and manager return objectives by rewarding the manager for outperformance. A performance fee is chargeable only when the portfolio performance exceeds the benchmark. Should the portfolio underperform it must first recover the underperformance since the performance fee last crystallised or the inception of the share class, whichever is later.',
        'The performance fee is calculated and accrued on a daily basis. If the performance conditions are no longer satisfied, all performance fees previously accrued during that accounting period (calendar year) are reversed.',
        'Included in the TER disclosure is performance fee for outperforming the benchmark. Subject to an overall minimum, the annual fee may be increased by the performance fee, calculated as the difference between the portfolio performance and the benchmark return for the same period, multiplied by the performance fee sharing rate.',
    ],
];

// Same worked example as 877 (identical sharing rate); only the title differs
// and it drops "GLOBAL" — "FOORD-HASSEN SHARIAH EQUITY FUND" per the reference.
$performanceFeeExamples = [
    'title' => 'PERFORMANCE FEE EXAMPLES FOR FOORD-HASSEN SHARIAH EQUITY FUND',
    'headers' => ['', 'PERIOD 1', 'PERIOD 2', 'PERIOD 3', 'PERIOD 4'],
    'rows' => [
        ['name' => 'Share class performance', 'values' => ['4.00%', '4.00%', '5.00%', '-4.00%']],
        ['name' => 'Benchmark performance', 'values' => ['6.00%', '2.00%', '3.00%', '-5.00%']],
        ['name' => 'Sub-fund’s GAV<sup>1</sup>', 'values' => ['$1,040,000', '$1,081,600', '$1,050,000', '$960,000']],
        ['name' => 'Notional NAV<sup>1</sup>', 'values' => ['$1,060,000', '$1,081,200', '$1,030,000', '$950,000']],
        ['name' => 'Outperformance', 'values' => ['No', '$400', '$20,000', '$10,000']],
        ['name' => 'Is a performance fee payable', 'values' => ['No', 'Yes', 'Yes', 'Yes']],
        ['name' => 'Performance fee accrual', 'values' => ['None', '$60<br>[$400 x 15%]', '$3,000<br>[$20,000 x 15%]', '$1,500<br>[$10,000 x 15%]']],
    ],
    'footnote' => '<sup>1</sup> The notional GAVs illustrated in the above table are based on the initial value of USD1,000,000. The notional GAVs get reset after a performance fee is fully crystalised.',
];

$sharePricing = [
    'title' => 'SHARE PRICING AND TRANSACTIONS',
    'text' => 'Shares will be issued or realised on a forward pricing basis only on Valuation Day (as defined in the prospectus) and calculated on the net asset value (“NAV) represented by one share. Prices are published on www.foord.com within two business days after the relevant Valuation Day. All dealing application requests must be received before 16h00 (Central European time) on each Valuation Day.',
];

$moreAboutFund = [
    'title' => 'MORE ABOUT THE FUND',
    'paragraphs' => [
        'The fund is authorised in Luxembourg and regulated by the Commission de Surveillance du Secteur Financier (CSSF). It is a medium-risk fund; rated 4 out of 7 using the Synthetic Risk and Reward Indictor (SRRI) calculation methodology guided by the European Commission. The fund is actively managed and not constrained by the benchmark in its portfolio positioning. Subject to the Shariah-compliant criteria adopted by the Shariah Supervisory Board, the Manager decides on the portfolio’s asset selection, regional allocation, sector views and overall level of exposure to the market to take advantage of investment opportunities. The fund can borrow up to 10% of NAV and does not engage in scrip lending. Since inception, no subscription fees or realisation fees were charged and no dividends or distributions were declared. Refer to the Key Information Document for more information.',
        'The Management Company may terminate the arrangements made for marketing of collective investment undertakings in accordance with Article 93a of Directive 2009/65/EC and Article 32a Directive 2011/611/EU. Please contact FundSight S.A. (formerly known as Lemanik Asset Management S.A.) on T: +352 26 39 60 or E: info@fundsight.com on regulatory matters. A summary of investor rights is available in English at www.foord.com.',
    ],
];

// ------------------------------------------------------------------ fund —
$fund = Fund::firstOrNew(['fund_code' => '878', 'class_code' => 'R']);

// Preserve importer-maintained values on re-runs.
$existingPerformanceTable = $fund->performance_table ?? [];
$existingPage2 = $fund->page2_content ?? [];

$fund->fill([
    'name' => 'FOORD-HASSEN SHARIAH GLOBAL EQUITY FUND — CLASS R',
    'class' => 'R',
    'template' => 'show-hassen-shariah',
    'fund_date' => $fund->fund_date ?? '31 July 2026',
    'description' => $description,
    'logo_url' => 'https://foord.co.za/themes/custom/mirum/logo.png',
    // Sidebar
    'domicile' => 'Luxembourg',
    'management_company' => 'FundSight S.A.',
    'shariah_supervisory_board' => 'Amanie Advisors Ltd',
    'depository' => 'CACEIS Bank, Luxembourg Branch',
    'investment_manager' => 'Foord Asset Management (Guernsey) Limited',
    'sub_investment_manager' => 'Foord Asset Management (Singapore) Pte. Limited',
    'fund_managers' => 'Ishreth Hassen',
    'inception_date' => '4 January 2021',
    'base_currency' => 'US dollars',
    'equity_indicator_description' => 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.',
    'category' => 'EAA Fund Islamic Global Equity',
    'benchmark' => 'MSCI All Country World Islamic (USD) Net Total Return Index',
    'type_of_shares' => 'Accumulation',
    'minimums' => 'US$10 000 or equivalent',
    'subsequent_subscription_amount' => 'US$1 000 or equivalent',
    'time_horizon' => 'Longer than five years.',
    'fees_summary' => 'Standard annual fee: 0.85%<br>Performance fee sharing rate: 15.0%<br>Maximum annual fee: uncapped',
    // Important info (page 2 sidebar)
    'important_info_title' => 'IMPORTANT INFORMATION FOR INVESTORS',
    'important_info_paragraphs' => $importantInfoParagraphs,
    // Footer — 878 prints both the phone and email lines (877 has T only).
    'footer_info' => 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.',
    'footer_free_of_charge' => 'This information is provided free of charge.',
    'footer_phone' => '+65 6521 1100 | +27 21 532 6969',
    'footer_email' => 'investments@foord.com',
    'footer_website' => '',
]);

$fund->user_id = $fund->user_id ?? $userId;

// Structured seeds — titles/headers the importer treats as seeded statics.
// The fund launched in 2021, so the table stops at 5 YRS (no 10 YRS column).
$fund->performance_table = array_merge($existingPerformanceTable, [
    'title' => 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)',
    'headers' => ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '5<br>YRS', '3<br>YRS', '1<br>YR', 'YTD', 'THIS<br>MONTH'],
    'columnKeys' => ['cashValue', 'sinceInception', '5yrs', '3yrs', '1yr', 'ytd', 'thisMonth'],
    'footnotes' => $footnotes,
]);

$topInvestments = $fund->top_investments ?? [];
$topInvestments['title'] = 'TOP 10 INVESTMENTS';
$topInvestments['headers'] = ['SECURITY', 'SECTOR', 'MARKET', '% OF FUND'];
$fund->top_investments = $topInvestments;

$sectorAllocation = $fund->sector_allocation ?? [];
$sectorAllocation['title'] = 'PORTFOLIO STRUCTURE %';
// DATA GAP: the 878 export sends ESAOT ranks 11-13 with no item name, no
// change and a zero variance, so the two zero-weight sectors the reference
// prints below the fed bars cannot be imported. They are seeded here and
// preserved across imports (the factsheet importer only rewrites
// sector_allocation['sectors']). Their variances need a manual monthly
// update until the feed carries the rows — flagged to the client.
$sectorAllocation['zeroWeightSectors'] = [
    ['name' => 'Communication services', 'variance' => '-0.4'],
    ['name' => 'Utilities', 'variance' => '-1.0'],
];
$fund->sector_allocation = $sectorAllocation;

$chartData = $fund->chart_data ?? [];
$chartData['title'] = 'PORTFOLIO PERFORMANCE VS BENCHMARK';
$fund->chart_data = $chartData;

$fund->page2_content = array_merge($existingPage2, [
    'performanceFees' => $performanceFees,
    'performanceFeeExamples' => $performanceFeeExamples,
    'sharePricing' => $sharePricing,
    'moreAboutFund' => $moreAboutFund,
]);

$fund->save();

echo "Seeded fund {$fund->id} — {$fund->name}\n";
