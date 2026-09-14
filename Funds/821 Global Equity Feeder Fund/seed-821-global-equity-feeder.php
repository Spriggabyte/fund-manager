<?php

/*
 * Seeds the Foord Global Equity Feeder Fund (821) — Classes A and B2.
 *
 * Run from the fund-manager root:
 *     php artisan tinker --execute="include 'Funds/821 Global Equity Feeder Fund/seed-821-global-equity-feeder.php';"
 *
 * Re-runnable: it upserts on (fund_code, class_code) and only writes the
 * static text that the monthly Excel exports do NOT carry. Everything the
 * feed owns — portfolio size, unit price, number of units, ISIN, the
 * portfolio-structure bars, top 10, chart series, performance rows, TIC and
 * the published date — is left to `php artisan fund:import`.
 *
 * Source of truth: the signed-off August 2026 Class A and B2 fact sheets in
 * this folder ("Foord Global Equity Feeder Fund Class {A,B2} at
 * 2026-08-31.pdf"). The two sheets differ only in per-class feed values, the
 * annual fee, and footnote 4's source line (A: Factset, B2: Bloomberg L.P.).
 *
 * The 821 export carries NO geographic-exposure keys, so the GEOGRAPHIC
 * EQUITY EXPOSURE chart is seeded here from the master fund's (877) export
 * for the same month and must be refreshed by hand each month — or the feed
 * extended. Flagged to the client.
 */

use App\Models\Fund;

$ownerId = Fund::query()->whereNotNull('user_id')->value('user_id') ?? 1;

// ---------------------------------------------------------------------------
// Shared static text (identical on both class sheets)
// ---------------------------------------------------------------------------

$description = 'The master fund aims to achieve long-term capital growth from an actively managed portfolio of global developed and emerging market equities and thereby outperform the MSCI All Country world equity index, without assuming greater risk. The fund is appropriate for South African investors with a long investment horizon, seeking capital growth from a portfolio that is diversified from SA-specific risks and who can withstand bouts of investment volatility in the short to medium term.';

// Word-for-word the 809 feeder sheet's page-2 sidebar (verified against the
// August 2026 821 references; B2's "T: 087361732" is a typo on that sheet).
$importantInfoParagraphs = [
    'Foord Unit Trusts (RF) (Pty) Ltd (Foord) is an approved CISCA Management Company (#10), regulated by the Financial Sector Conduct Authority. Portfolios are managed by Foord Asset Management (Pty) Ltd, an authorised Financial Services Provider (FSP: 578). The custodian/trustee of Foord Unit Trusts is RMB Custody and Trustee Services (a division of FirstRand Bank Limited), contactable on T: 0877361732, www.rmb.co.za.',
    'Collective Investment Schemes in Securities (unit trusts) are generally medium- to long-term investments. The value of participatory interests (units) may go down as well as up and past performance is not necessarily a guide to the future. Performance is calculated for the portfolio. Individual investor performance may differ as a result of the actual investment date, the date of reinvestment and withholding taxes. Performance may be affected by changes in the market or economic conditions and legal, regulatory and tax requirements. Foord does not provide any guarantee either with respect to the capital or the performance return of the investment. Unit trusts are traded at ruling prices and can engage in borrowing. Foord does not engage in scrip lending. Commission and incentives may be paid and if so, this cost is not borne by the investor. A schedule of fees and charges and maximum commissions is available on request. Distributions may be subject to mandatory withholding taxes. Portfolio information is presented using effective exposures. A fund of funds invests only in other Collective Investment Scheme portfolios, which may levy their own charges, which could result in a higher fee structure. A feeder fund is a portfolio that, apart from assets in liquid form, consists solely of units in a single portfolio of a Collective Investment Scheme which could result in a higher fee structure. Foord is authorised to close the portfolio to new investors in order to manage the portfolio more efficiently in accordance with its mandate.',
    'Unit trust prices are calculated on a net asset value basis, which is the total value of all assets in the portfolio including any income accruals and less any permissible deductions from the portfolio. Forward pricing is used. Prices are determined at 15h00 each business day and are published daily on www.foord.co.za. The cut-off time for instruction is 14h00 each business day.',
    'The portfolio may include underlying foreign investments. Fluctuations or movements in exchange rates may cause the value of underlying foreign investments to go up or down. The underlying foreign investments may be adversely affected by political instability as well as exchange controls, changes in taxation, foreign investment policies, restrictions on repatriation of investments and other restrictions and controls that may be imposed by the relevant authorities in the relevant countries.',
    'This document is not an advertisement, but is provided exclusively for information purposes and is not an offer or solicitation to purchase, sell or otherwise deal with any particular investment. Economic forecasts and predictions are based on Foord’s interpretation of current factual information and exploration of economic activity based on expectation for future growth under normal economic conditions, not dissimilar to previous cycles. Forecasts and commentaries are provided for information purposes only and are not guaranteed to occur. While we have taken and will continue to take care that the information contained herein is true and correct, we request that you report any errors to Foord at unittrusts@foord.co.za. The document is protected by copyright and may not be altered without prior written consent.',
    'Foord Asset Management is a member of the Association for Savings and Investment SA.',
    'This is a Minimum Disclosure Document.',
    'Additional detailed analysis is published in the Quarterly Portfolio Report available on www.foord.co.za.',
];

// Nine columns: the 809 feeder's set minus 15 YRS.
$performanceHeaders = ['', 'CASH VALUE²', 'SINCE<br>INCEPTION', '10<br>YRS', '7<br>YRS', '5<br>YRS', '3<br>YRS', '1<br>YR', 'THIS<br>MONTH'];
$performanceColumnKeys = ['cashValue', 'sinceInception', '10yrs', '7yrs', '5yrs', '3yrs', '1yr', 'thisMonth'];

// Footnote 4 names a different source on each class sheet — reproduced as
// published.
$performanceFootnotes = function (string $benchmarkSource): array {
    return [
        '<sup>1</sup> Converted to reflect the average yearly return for each period presented',
        '<sup>2</sup> Current value of R100 000 notional lump sum invested at inception, distributions reinvested (graphically represented in R’000s above)',
        '<sup>3</sup> Net of fees and expenses',
        '<sup>4</sup> Source: '.$benchmarkSource.', performance as calculated by Foord',
        '<sup>5</sup> Global Large-Cap Blend Equity Morningstar category (provisional). Source: Morningstar',
        '<sup>6</sup> Highest and lowest actual 12 month rand return achieved in the period',
        '<sup>7</sup> Source: Factset and internal',
        'Note: Totals may not cast perfectly due to rounding',
    ];
};

// GEOGRAPHIC EQUITY EXPOSURE — no 821 feed keys; values are the master
// fund's GEO_EXP_*_EQTY / _EQTY_BM rows from the 877 export of the same
// month (July 2026 here). Refresh by hand with each monthly import.
$geographicEquityExposure = [
    ['name' => 'North America', 'fund' => 31, 'benchmark' => 65],
    ['name' => 'EM Asia', 'fund' => 30, 'benchmark' => 9],
    ['name' => 'Europe', 'fund' => 33, 'benchmark' => 15],
    ['name' => 'Pacific', 'fund' => 6, 'benchmark' => 8],
];

$sharedSidebar = [
    'domicile' => 'South Africa',
    'management_company' => 'Foord Unit Trusts (RF) (Pty) Ltd<br>VAT Registration Number: 4560201594',
    // The reference lets this wrap naturally ("… and Jing / Cong Xue").
    'fund_managers' => 'Brian Arcese, Ishreth Hassen and Jing Cong Xue',
    'inception_date' => '2 May 2014',
    'base_currency' => 'South African rands',
    'equity_indicator_description' => 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.',
    // Em dashes, as printed.
    'category' => 'Global — Equity — General',
    'benchmark' => 'The ZAR equivalent of MSCI All Country World Total Return Index.',
    'minimums' => 'Fund is closed to new investment',
    'last_distributions' => 'The Foord Global Equity Fund, in which the fund invests, does not distribute its income.',
    'income_characteristics' => 'Marginal to zero income yield as the Foord Global Equity Fund is a roll-up fund and does not distribute its income.',
    'portfolio_orientation' => 'Invests in Foord Global Equity Fund, a sub-fund of the Foord SICAV, a fund invested primarily in a diversified portfolio of global equities, priced in US dollars and domiciled in Luxembourg.',
    'significant_restrictions' => 'The portfolio may only invest in cash and one other collective investment scheme. The master fund complies with the European Union’s UCITS asset spreading rules and may only use derivative instruments for hedging or efficient portfolio management.',
    'risk_of_loss' => 'Currency volatility means risk of loss in the short term is high. In general, the risk is high in periods shorter than one year and lower in periods longer than three years.',
    'time_horizon' => 'Longer than five years.',
    // `foreign_assets` carries the RETURNS IN US$ note (see the template's
    // sidebar label map).
    'foreign_assets' => 'Investment returns in US$ may not reconcile exactly to those of Foord Global Equity Fund as pricing within the feeder fund lags by one valuation interval.',
];

$footer = [
    'footer_info' => 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.',
    'footer_free_of_charge' => 'This information is provided free of charge.',
    'footer_phone' => '+27 21 532 6969',
    'footer_email' => 'unittrusts@foord.co.za',
    'footer_website' => 'www.foord.co.za',
];

// ---------------------------------------------------------------------------
// Per-class values (everything the two sheets disagree on, minus feed data)
// ---------------------------------------------------------------------------

$classes = [
    'A' => ['annual_fee' => '0.35% plus VAT', 'benchmark_source' => 'Factset'],
    'B2' => ['annual_fee' => '0.1% plus VAT', 'benchmark_source' => 'Bloomberg L.P.'],
];

foreach ($classes as $classCode => $classData) {
    $fund = Fund::firstOrNew(['fund_code' => '821', 'class_code' => $classCode]);

    $fund->fill($sharedSidebar);
    $fund->fill($footer);

    $fund->user_id = $fund->user_id ?? $ownerId;
    $fund->name = 'FOORD GLOBAL EQUITY FEEDER FUND — CLASS '.$classCode;
    $fund->class = $classCode;
    $fund->template = Fund::GLOBAL_EQUITY_FEEDER_TEMPLATE;
    $fund->description = $description;
    $fund->logo_url = 'https://foord.co.za/themes/custom/mirum/logo.png';

    $fund->important_info_title = 'IMPORTANT INFORMATION FOR INVESTORS';
    $fund->important_info_paragraphs = $importantInfoParagraphs;

    // Performance table: the import replaces `rows` every month, so only the
    // title/headers/columnKeys/footnotes are seeded here.
    $performanceTable = $fund->performance_table ?? [];
    $performanceTable['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)';
    $performanceTable['headers'] = $performanceHeaders;
    $performanceTable['columnKeys'] = $performanceColumnKeys;
    $performanceTable['footnotes'] = $performanceFootnotes($classData['benchmark_source']);
    $fund->performance_table = $performanceTable;

    // Top 10: the import replaces `rows` but preserves an existing title and
    // headers — the reference's second column reads SECTOR, not the feed
    // default's ASSET CLASS.
    $topInvestments = $fund->top_investments ?? [];
    $topInvestments['title'] = 'TOP 10 INVESTMENTS';
    $topInvestments['headers'] = ['SECURITY', 'SECTOR', 'MARKET', '% OF FUND'];
    $fund->top_investments = $topInvestments;

    // Page 1's bar list: the import replaces `sectors` and refreshes the
    // "Change since …" subtitle, but preserves an existing title.
    $sectorAllocation = $fund->sector_allocation ?? [];
    $sectorAllocation['title'] = 'PORTFOLIO STRUCTURE %';
    $fund->sector_allocation = $sectorAllocation;

    // Geographic equity exposure rides in the asset_allocation JSON (the
    // 821 feed has no AAOT_* rows, so nothing else lives there).
    $assetAllocation = $fund->asset_allocation ?? [];
    $assetAllocation['geographicEquityExposure'] = $geographicEquityExposure;
    $fund->asset_allocation = $assetAllocation;

    // Charts: the import replaces `performanceData`; the title is static.
    $chartData = $fund->chart_data ?? [];
    $chartData['title'] = 'PORTFOLIO PERFORMANCE VS BENCHMARK';
    $fund->chart_data = $chartData;

    // Fee rates are static per class; the TIC table comes from the feed and
    // the importer refreshes the TER sentence at the end of the description.
    $fees = $fund->fees ?? [];
    $fees['feeRates'] = [
        'title' => 'FEE RATES',
        'rates' => [
            ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
            ['name' => 'Annual fee', 'value' => $classData['annual_fee']],
        ],
        'globalFunds' => [
            'title' => 'Foord global funds:',
            'funds' => [
                ['name' => '- Foord Global Equity Fund', 'value' => '0.85% fixed annual fee plus 15% performance fee'],
            ],
        ],
    ];
    $fees['totalInvestmentCharge'] = array_merge($fees['totalInvestmentCharge'] ?? [], [
        'title' => 'TOTAL INVESTMENT CHARGE %',
        'headers' => ['', '12 MONTHS', '36 MONTHS'],
        'description' => $fees['totalInvestmentCharge']['description']
            ?? 'A TER is a measure of a portfolio’s annual expenses, fees and charges, expressed as a percentage of the average daily value of the portfolio. These expenses include the annual fee, VAT, audit fees, bank charges and costs (excluding trading costs) incurred in any underlying funds. Included in the TER, but separately disclosed, is a performance fee (or credit) resulting from overperformance (or underperformance) against the benchmark. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TERs. Performance return information and prices are always stated net of the expenses, fees and charges included in the TER. The TER for the fund’s financial year ended 31 March 2026 was 1.49%.',
    ]);
    $fund->fees = $fees;

    $fund->page2_content = array_merge($fund->page2_content ?? [], [
        'investingOffshore' => [
            'title' => 'INVESTING OFFSHORE',
            // "withdraw in rands" — the 809 sheet drops the preposition.
            'text' => 'While an investment in the fund provides for global asset exposure, you may only invest and withdraw in rands. Your contribution to a feeder fund does not utilise your offshore exchange control allowances.',
        ],
    ]);

    $fund->save();

    echo 'Seeded fund '.$fund->id.' — 821 Class '.$classCode.PHP_EOL;
}
