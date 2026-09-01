<?php

// Seed script — Foord International Trust Class B (874).
// Statics transcribed verbatim from the published reference:
// "Funds/874 - International Trust/Foord International Trust Class B at
//  2026-07-31.pdf" (including the reference's one straight apostrophe in
// "the fund's domicile" and the mid-sentence paragraph break in MORE ABOUT
// THE FUND). Footer and logo cloned from fund 14 (875 Class R), which
// matches the 874 reference verbatim. Run via tinker `include`; re-runnable
// (updates in place). Import afterwards:
//   php artisan fund:import <id> "storage/app/private/fund-data/<YYYY-MM>/874"

use App\Models\Fund;

$source = Fund::findOrFail(14); // 875 Class R — footer/logo donor

$fund = Fund::where('fund_code', '874')->where('class_code', 'B')->first();
if (! $fund) {
    $fund = new Fund;
    $fund->user_id = $source->user_id;
}

$fund->name = 'FOORD INTERNATIONAL TRUST — CLASS B';
$fund->fund_code = '874';
$fund->class_code = 'B';
$fund->template = 'show-international-trust';
$fund->fund_date = '31 July 2026';
$fund->description = 'The master fund aims to achieve meaningful inflation-beating US dollar returns over the long term from a conservative, but actively managed, multi-asset class portfolio of global developed and emerging market securities reflecting Foord’s prevailing best investment view. The fund is appropriate for investors with a moderate risk profile, seeking preservation of capital and safe investment growth with a time horizon of at least three years.';
$fund->logo_url = $source->logo_url;

// Sidebar statics (reference page 1, top to bottom)
$fund->master_fund = 'The fund invests exclusively in Class B shares of Foord SICAV-Foord International Fund (the “Master Fund”).';
$fund->domicile = 'Guernsey';
// The reference wraps these with Publisher's manual breaks
$fund->investment_manager = 'Foord Asset Management (Guernsey)<br>Limited';
$fund->fund_managers = 'Brian Arcese, Dave Foord and<br>Jing Cong Xue';
$fund->inception_date = '10 March 1997';
$fund->base_currency = 'US dollars';
$fund->equity_indicator_description = 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.';
$fund->category = 'USD Flexible Allocation';       // MORNINGSTAR CATEGORY
$fund->type_of_shares = 'Accumulation';            // TYPE OF UNITS
$fund->minimums = 'US$1 000 or equivalent';        // MINIMUM INVESTMENT
$fund->time_horizon = 'Longer than three years';
$fund->fees_summary = 'None in the fund. A 1.00% per annum fixed management fee is levied in the master fund.';
$fund->master_fund_returns = 'Investment returns may not reconcile exactly to those of the Master Fund as pricing within the Foord International Trust lags by one valuation interval prior to 1 July 2017.';
// portfolio_size / unit_price / number_of_units / isin_number / sedol come
// from the factsheet import.

// Asset allocation title carries the bracketed qualifier; rows, the
// geographic exposure table and the equity sector bars come from the import.
$assetAllocation = $fund->asset_allocation ?? [];
$assetAllocation['title'] = 'ASSET ALLOCATION % (Effective exposure)';
$fund->asset_allocation = $assetAllocation;

// Performance table statics — rows come from the import.
$performanceTable = $fund->performance_table ?? [];
$performanceTable['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)¹';
$performanceTable['headers'] = ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '10<br>YRS', '5<br>YRS', '3<br>YRS', '1<br>YR', 'YTD', 'THIS<br>MONTH'];
$performanceTable['columnKeys'] = ['cashValue', 'sinceInception', '10yrs', '5yrs', '3yrs', '1yr', 'ytd', 'thisMonth'];
// NOTES 1–8 + the two unnumbered closing lines (reference page 2, verbatim).
$performanceTable['footnotes'] = [
    '<sup>1</sup> Returns in USD unless otherwise stated and annualised for periods greater than one year, meaning they are converted to reflect the average yearly return for each period presented.',
    '<sup>2</sup> Current value of 100 000 notional currency units invested at inception (graphically represented in $’000s above).',
    '<sup>3</sup> Performance, net of fees and expenses, is calculated for the portfolio on a single pricing basis (i.e. NAV to NAV rolling monthly basis). Individual investor performance may differ as a result of the actual investment date. Past performance of the fund is not indicative of its future performance.',
    '<sup>4</sup> USD Flexible Allocation Morningstar category (provisional). Source: Morningstar.',
    '<sup>5</sup> US headline consumer price index. Source: Bloomberg L.P. (lagged by one month).',
    '<sup>6</sup> MSCI Daily Total Return Net World USD Index. Prior to April 2016, MSCI World Equity Total Return Index (Developed Markets) was presented. Comparative periods have been restated. Source: Bloomberg L.P.',
    '<sup>7</sup> FTSE World Government Bond Index. Source: Bloomberg L.P.',
    '<sup>8</sup> Highest and lowest actual 12-month return achieved in this period.',
    'The portfolio information is presented using effective exposures, unless stated otherwise.',
    'Totals may not cast perfectly due to rounding.',
];
$fund->performance_table = $performanceTable;

// ANNUALISED COST RATIO % — the table values refresh from every GLOBAL_TER
// import; the paragraph is static. The "latest audited TER" figure is an
// audited annual value not on the feed (the feed's
// TER_FOR_FUND_FINANCIAL_YEAR_END exports 1.03% against the published
// 1.01%) — maintain it by inline edit.
$fees = $fund->fees ?? [];
$acr = $fees['annualisedCostRatio'] ?? [];
$acr['description'] = 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average value of the portfolio. Where the fund has invested significantly into an underlying fund the TER may include the TER of that underlying fund. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. The latest audited TER is 1.01%. The quantum of transaction costs is affected by the quantum of the gross in and outflows over the period presented. A schedule of fees and charges is available on request.';
$fees['annualisedCostRatio'] = $acr;
$fund->fees = $fees;

// Page 2 narrative sections
$fund->page2_content = [
    'sharePricing' => [
        'title' => 'UNIT PRICING AND TRANSACTIONS',
        'text' => 'Units will be issued or realised on a forward pricing basis only on Dealing Day (as defined in the prospectus) and calculated based on the net asset value (“NAV”) represented by one unit. Prices are published on www.foord.com within two business days after the relevant Dealing Day. All dealing application requests must be received before 16h00 (Central European time) on each Dealing Day.',
    ],
    'moreAboutFund' => [
        'title' => 'MORE ABOUT THE FUND',
        'paragraphs' => [
            'JTC Global AIFM Solutions Limited is the trustee of Foord International Trust, an authorised Class B Collective Investment Scheme under the Protection of Investors (Bailiwick of Guernsey) Law, 2020. The trustee is contactable on T: +44 1481 702 400, F: +44 1481 702 407. Foord Asset Management (Guernsey) Limited, the Principal Manager, is regulated by the Guernsey Financial Services Commission. A summary of investor rights is available in English at www.foord.com.',
            // The published sheet breaks this sentence across two paragraphs
            // ("…borrow up to 10% of" / "NAV and does not engage…").
            'The fund/ Master Fund is an actively managed fund without reference to a benchmark. The Manager decides on the portfolio’s asset selection, regional allocation, sector views and overall level of exposure to the market to take advantage of investment opportunities. The fund can borrow up to 10% of',
            'NAV and does not engage in scrip lending. Since inception, no subscription fees or realisation fees were charged and no dividends or distributions were declared.',
        ],
    ],
];

// IMPORTANT INFORMATION FOR INVESTORS (page-2 sidebar, verbatim)
$fund->important_info_title = 'IMPORTANT INFORMATION FOR INVESTORS';
$fund->important_info_paragraphs = [
    'This is a marketing communication. Investors should read the prospectus available in English at www.foord.com, and seek professional advice or consider investment suitability before investing in the fund. This document is not an advertisement but is provided for information purposes and should not be regarded as an offer or solicitation to purchase, sell or otherwise deal in the fund.',
    'Collective investment schemes in securities are generally medium to long term investments. Foord does not guarantee the capital invested or the performance of the investment. The portfolio includes qualifying investments listed on regulated exchanges outside the fund\'s domicile that carry risks as described in the prospectus, including the possibility of non-recoverable withholding taxes and non-repatriation of funds. Investment values and some costs may fluctuate because of factors including but not limited to currency exchange rates that can be affected by a wide range of economic factors.',
    'Economic forecasts and predictions are based on Foord’s interpretation of current factual information, and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur.',
    'While we have taken and will continue to take care that the information contained herein is true and correct, we do not guarantee the accuracy, timeliness or completeness of the information provided, and therefore disclaim any liability, damage (whether direct or consequential) or expense suffered as a result of reliance on the information.',
    'The document is protected by copyright and may not be altered without Foord’s consent.',
    'Note: For South African investors, this document is a Minimum Disclosure Document.',
];
// important_info_published_date comes from the factsheet import.

// Footer — cloned from fund 14, identical in the 874 reference.
$fund->footer_info = $source->footer_info;
$fund->footer_phone = $source->footer_phone;
$fund->footer_email = $source->footer_email;
$fund->footer_website = $source->footer_website;
$fund->footer_free_of_charge = $source->footer_free_of_charge;
$fund->footer_logo_url = $source->footer_logo_url;

$fund->save();

echo "Seeded fund {$fund->id}: {$fund->name}\n";
