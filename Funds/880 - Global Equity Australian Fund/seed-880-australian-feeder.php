<?php

/**
 * Seed the Foord Global Equity Australian Feeder Fund — 880 Class A.
 *
 * Statics transcribed from the signed-off reference
 * ("Design/Foord Global Equity Australian Feeder Fund at 2026-07-31
 * (reference).pdf"; the deliverable sits at the folder root). The sheet
 * is the Global Equity (Luxembourg) page layout (see
 * Funds/878 …/seed-878-hassen-shariah.php) reworked for an Australian feeder:
 * the sidebar names the responsible entity, custodian and distribution
 * partner instead of the management company and depositary, carries no equity
 * indicator, and closes with the Zenith approval mark; page 1 adds the SINCE
 * 11 AUG 22 performance column; page 2 drops the cost-ratio and
 * performance-fee blocks entirely.
 *
 * Every scalar the feed carries (share price, number of units, portfolio size,
 * tables, chart series) refreshes from the monthly exports via fund:import —
 * this file seeds only what the exports omit.
 *
 * Run (idempotent, re-runnable):
 *   php artisan tinker --execute='include "Funds/880 - Global Equity Australian Fund/seed-880-australian-feeder.php";'
 */

use App\Models\Fund;

$userId = Fund::where('fund_code', '878')->value('user_id') ?? Fund::query()->value('user_id');

// ---------------------------------------------------------------- statics —
$description = 'The master fund aims to achieve long-term capital growth from an actively managed and diversified portfolio of global developed and emerging market equities and to thereby outperform its MSCI All Country world equity benchmark, without assuming greater risk. The fund is appropriate for investors with a long investment horizon and who can withstand bouts of investment volatility in the short to medium term.';

// The reference mixes straight and curly quotes between paragraphs; both are
// transcribed as printed.
$importantInfoParagraphs = [
    'This is a marketing communication. Investors should read the Product Disclosure Statement ("PDS") — available in English at www.foord.com — and seek professional advice or consider investment suitability before investing in the fund. This document is not an advertisement but is provided for information purposes and should not be regarded as an offer or solicitation to purchase, sell or otherwise deal in the fund.',
    'Foord does not guarantee the capital invested or the performance of the investment. The portfolio includes qualifying investments listed on regulated exchanges outside the fund\'s domicile that carry risks as described in the prospectus, including the possibility of non-recoverable withholding taxes and non-repatriation of funds. Investment values and some costs may fluctuate because of factors including but not limited to currency exchange rates that can be affected by a wide range of economic factors.',
    'Economic forecasts and predictions are based on Foord’s interpretation of current factual information, and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur.',
    'While we have taken and will continue to take care that the information contained herein is true and correct, neither Foord, Equity Trustees nor any of their related parties, employees or directors, provide any warranty of accuracy or reliability in relation to such information or accepts any liability to any person who relies on it.',
    'The document is protected by copyright and may not be altered without Foord’s consent.',
];

$footnotes = [
    '<sup>1</sup> Returns net in AUD and annualised for periods greater than one year, meaning they are converted to reflect the average yearly return for each period presented. Past performance is not indicative of its future performance.',
    '<sup>2</sup> Current value of 100 000 notional currency units invested at inception (graphically represented in A$’000s above).',
    '<sup>3</sup> Australia Fund Equity World Large Blend (provisional). Source: Morningstar.',
    '<sup>4</sup> Returns prior to 11 August 2022 include the Master Fund’s past performance, in AUD equivalent based on pre­vailing foreign exchange rates. The Master Fund, a Luxembourg-domiciled UCITS fund incepted on 2 April 2013, has the same investment objectives and policies, comparable cost structure.',
    '<sup>5</sup> Source: Bloomberg and internal.',
    'The portfolio information is presented using effective exposure.',
    'Totals may not cast perfectly due to rounding.',
];

// Page 2 leads with the unit-pricing block where the Luxembourg sheets carry
// SHARE PRICING AND TRANSACTIONS.
$unitPricing = [
    'title' => 'UNIT PRICING AND TRANSACTIONS',
    'text' => 'Application or Withdrawal price is determined in accordance with the Fund’s Constitution on a Business Day. In general terms, the price is equal to the Net Asset Value (“NAV”) of the Fund, divided by the number of units in issue and adjusted for current transaction costs of 0.15% which is also know as the buy/sell spread.',
];

$moreAboutFund = [
    'title' => 'MORE ABOUT THE FUND',
    'paragraphs' => [
        'The responsible entity, Equity Trustees Limited (“Equity Trustees”) (ABN 46 004 031 298/ AFSL 240975), is a subsidiary of EQT Holdings Limited (ABN 22 607 797 615) listed on the Australian Securities Exchange (ASX: EQT). Neither Equity Trustee or any of its related parties, their employees or directors, provide any warranty of accuracy or reliability in relation to such information or accepts any liability to any person who relies on it.',
        'The fund invests exclusively in Class B shares of Foord SICAV - Foord Global Equity Fund (Luxembourg) (the "Master Fund"). The Master Fund is actively managed and not constrained by the benchmark in its portfolio positioning. The Manager decides on the portfolio\'s asset selection, regional allocation, sector views and overall level of exposure to the market to take advantage of investment opportunities. The Master Fund can borrow up to 10% of NAV and does not engage in scrip lending. Since inception, no dividends or distributions were declared.',
        'This document is prepared by the investment manager which is licensed as a foreign Australian Financial Services Licensee (AFSL 542514) to provide financial services to wholesale clients in Australia.',
    ],
];

// ------------------------------------------------------------------ fund —
$fund = Fund::firstOrNew(['fund_code' => '880', 'class_code' => 'A']);

// Preserve importer-maintained values on re-runs.
$existingPerformanceTable = $fund->performance_table ?? [];
$existingPage2 = $fund->page2_content ?? [];

$fund->fill([
    'name' => 'FOORD GLOBAL EQUITY AUSTRALIAN FEEDER FUND — CLASS A',
    'class' => 'A',
    'template' => 'show-australian-feeder',
    'fund_date' => $fund->fund_date ?? '31 July 2026',
    'description' => $description,
    'logo_url' => 'https://foord.co.za/themes/custom/mirum/logo.png',
    // Sidebar
    'domicile' => 'Australia',
    'responsible_entity' => 'Equity Trustees Limited',
    'custodian' => 'Mainstream Fund Services Pty Ltd',
    'investment_manager' => 'Foord Asset Management (Singapore) Pte. Limited',
    'fund_managers' => 'Brian Arcese, Ishreth Hassen and Jing Cong Xue',
    'distribution_partner' => 'Shed Enterprises Pty Limited',
    'inception_date' => '11 August 2022',
    // The feeder reports in Australian dollars; the master fund it holds is
    // US-dollar based, named on a second line.
    'base_currency' => 'Australian dollar (A$)<br>Master fund: US dollar',
    'category' => 'Australia Fund Equity World Large Blend',
    'benchmark' => 'MSCI All Country World Net Total Return Index (AUD)',
    'type_of_shares' => 'Accumulation',
    'minimums' => 'US$1 000 000 or equivalent',
    'subsequent_subscription_amount' => 'US$100 000',
    'time_horizon' => 'Longer than five years.',
    'fund_features' => 'Multiple-counsellor approach<br>Risk aware and active<br>Long only, high conviction<br>Benchmark agnostic',
    'fees_summary' => 'Management fees: 1.00% is levied in the Master Fund<br>Indirect costs: 0.20%',
    // The 880 export has no ISIN column (it sends "0"); both registration
    // rows are statics.
    'isin_number' => 'AU60ETL37743',
    'apir_arsn' => 'ETL3774AU<br>659 724 286',
    // Important info (page 2 sidebar)
    'important_info_title' => 'IMPORTANT INFORMATION FOR INVESTORS',
    'important_info_paragraphs' => $importantInfoParagraphs,
    // Footer — 880 prints the phone, email and website lines.
    'footer_info' => 'Please visit our website for more information regarding our investment track record, our Foord team, current and archived news items, or forms and documents.',
    'footer_free_of_charge' => 'This information is provided free of charge.',
    'footer_phone' => '+65 6521 1100 | +27 21 532 6969',
    'footer_email' => 'investments@foord.com',
    'footer_website' => 'www.foord.com',
]);

$fund->user_id = $fund->user_id ?? $userId;

// Structured seeds — titles/headers the importer treats as seeded statics.
// SINCE INCEPTION runs from the master fund's launch (2 April 2013) and
// SINCE 11 AUG 22 from this class's; see PriceGraphImporter.
$fund->performance_table = array_merge($existingPerformanceTable, [
    'title' => 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)',
    'headers' => ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '5<br>YRS', '3<br>YRS', '1<br>YR', 'YTD', 'THIS<br>MONTH', 'SINCE 11<br>AUG 22'],
    'columnKeys' => ['cashValue', 'sinceInception', '5yrs', '3yrs', '1yr', 'ytd', 'thisMonth', 'sinceClassInception'],
    'footnotes' => $footnotes,
]);

$topInvestments = $fund->top_investments ?? [];
$topInvestments['title'] = 'TOP 10 INVESTMENTS';
$topInvestments['headers'] = ['SECURITY', 'SECTOR', 'MARKET', '% OF FUND'];
$fund->top_investments = $topInvestments;

$sectorAllocation = $fund->sector_allocation ?? [];
$sectorAllocation['title'] = 'PORTFOLIO STRUCTURE %';
$fund->sector_allocation = $sectorAllocation;

$chartData = $fund->chart_data ?? [];
$chartData['title'] = 'PORTFOLIO PERFORMANCE VS BENCHMARK';
$fund->chart_data = $chartData;

$fund->page2_content = array_merge($existingPage2, [
    'sharePricing' => $unitPricing,
    'moreAboutFund' => $moreAboutFund,
]);

$fund->save();

echo "Seeded fund {$fund->id} — {$fund->name}\n";
