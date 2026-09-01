<?php

/*
 * Seeds the Prescient Foord International Feeder Fund (822) — Classes A and B2.
 *
 * Run from the fund-manager root:
 *     php artisan tinker --execute="include 'Funds/822 - Prescient International Feeder Fund/seed-822-prescient-feeder.php';"
 *
 * Re-runnable: it upserts on (fund_code, class_code) and only writes the
 * static text that the monthly Excel exports do NOT carry. Everything the
 * feed owns — portfolio size, unit price, number of units, ISIN, tables,
 * chart series, TIC — is left to `php artisan fund:import`.
 *
 * Source of truth: the signed-off July 2026 reference PDFs in ./Design/.
 */

use App\Models\Fund;

$ownerId = Fund::query()->whereNotNull('user_id')->value('user_id') ?? 1;

// ---------------------------------------------------------------------------
// Shared static text (identical on both class sheets)
// ---------------------------------------------------------------------------

$description = 'The master fund aims to achieve meaningful inflation-beating US dollar returns over the long term from a conservatively managed, multi-asset class portfolio of global developed and emerging market securities. The fund is appropriate for South African investors with a moderate risk profile, seeking preservation of capital and safe investment growth from a hard-currency portfolio that is diversified from SA-specific risks.';

$importantInfoParagraphs = [
    'Collective investment Schemes in Securities (CIS) should be considered as medium to long-term investments. The value may go up as well as down and past performance is not necessarily a guide to future performance. CIS’s are traded at the ruling price and can engage in scrip lending and borrowing. The collective investment scheme may borrow up to 10% of the market value of the portfolio to bridge insufficient liquidity. A schedule of fees, charges and maximum commissions is available on request from the Manager. There is no guarantee in respect of capital or returns in a portfolio. A CIS may be closed to new investors in order for it to be managed more efficiently in accordance with its mandate. CIS prices are calculated on a net asset basis, which is the total value of all the assets in the portfolio including any income accruals and less any permissible deductions (brokerage, STT, VAT, auditor’s fees, bank charges, trustee and custodian fees and the annual management fee) from the portfolio divided by the number of participatory interests (units) in issue. Forward pricing is used.',
    'A Feeder Fund is a portfolio that invests in a single portfolio of a collective investment scheme which levies its own charges, and which could result in a higher fee structure for the feeder fund.',
    'The Manager retains full legal responsibility for any third-party-named portfolio. Where foreign securities are included in a portfolio there may be potential constraints on liquidity and the repatriation of funds, macroeconomic risks, political risks, foreign exchange risks, tax risks, settlement risks, and potential limitations on the availability of market information. The investor acknowledges the inherent risk associated with the selected investments and that there are no guarantees. Please note that all documents, notifications of deposit, investment, redemption and switch applications must be received by Prescient by or before 13:00 (SA), to be transacted at the net asset value price for that day. Where all required documentation is not received before the stated cut off time Prescient shall not be obliged to transact at the net asset value price as agreed to. Funds are priced at either 3pm or 5pm depending on the nature of the Fund. Prices are published daily and are available on the Prescient website.',
    'Performance has been calculated using net NAV to NAV numbers with income reinvested. The performance for each period shown reflects the return for investors who have been fully invested for that period. Individual investor performance may differ as a result of initial fees, the actual investment date, the date of reinvestments and dividend withholding tax. Full performance calculations are available from the manager on request.',
    'The investment performance is for illustrative purposes only. The investment performance is calculated by taking the actual initial fees and all ongoing fees into account for the amount shown and income is reinvested on the reinvestment date.',
    'For any additional information such as fund prices, brochures and application forms please go to www.prescient.co.za.',
    // Renders in grey italics — the template styles the closing paragraph.
    'This document is for information purposes only and does not constitute or form part of any offer to issue or sell or any solicitation of any offer to subscribe for or purchase any particular investments. Opinions expressed in this document may be changed without notice at any time after publication. We therefore disclaim any liability for any loss, liability, damage (whether direct or consequential) or expense of any nature whatsoever which may be suffered as a result of or which may be attributable directly or indirectly to the use of or reliance upon the information.',
];

$performanceHeaders = ['', 'CASH VALUE²', 'SINCE<br>INCEPTION', '3<br>YEARS', '1<br>YEAR', '6<br>MONTHS', 'YEAR TO<br>DATE'];
$performanceColumnKeys = ['cashValue', 'sinceInception', '3yrs', '1yr', '6months', 'ytd'];

$performanceFootnotes = [
    '<sup>1</sup> Converted to reflect the average yearly return for each period presented',
    '<sup>2</sup> Current value of R100 000 notional lump sum invested at inception, net of fees and distributions reinvested on the reinvestment date (graphically represented in R’000s above).',
    '<sup>3</sup> Net of fees and expenses',
    '<sup>4</sup> USD Flexible Allocation Morningstar category (provisional). Source: Morningstar',
    '<sup>5</sup> US headline consumer price index. Source: Bloomberg L.P., performance as calculated by Foord (lagged by one month)',
    // The Class A sheet credits Bloomberg L.P. here and the Class B2 sheet
    // credits Factset; the 809 sheet and B2 agree on Factset, so both classes
    // carry Factset. Flagged for the client.
    '<sup>6</sup> MSCI World Equity Total Return Index (Developed Markets). Source: Factset, performance as calculated by Foord',
    '<sup>7</sup> FTSE World Government Bond Index. Source: Bloomberg L.P., performance as calculated by Foord',
    '<sup>8</sup> Highest and lowest actual 12 month rand return achieved in the period',
    'The portfolio information is presented using effective exposure, unless stated otherwise.',
    'Note: Totals may not cast perfectly due to rounding',
];

$contactDetails = [
    'title' => 'CONTACT DETAILS',
    'blocks' => [
        [
            ['label' => 'Management Company', 'value' => 'Prescient Management Company (RF) (Pty) Ltd,'],
            ['label' => 'Registration number', 'value' => '2002/022560/07'],
            ['label' => 'Physical address', 'value' => 'Prescient House, Westlake Business Park, Otto Close, Westlake 7945'],
            ['label' => 'Postal address', 'value' => 'PO Box 31142, Tokai, 7966.'],
            ['label' => 'Telephone number', 'value' => '0800 111 899.'],
            ['label' => 'E-mail address', 'value' => 'info@prescient.co.za'],
            ['label' => 'Website', 'value' => 'www.prescient.co.za'],
        ],
        [
            ['label' => 'Trustee', 'value' => 'Nedbank Investor Services'],
            ['label' => 'Physical address', 'value' => '2nd Floor, 16 Constantia Boulevard, Constantia Kloof, Roodepoort, 1709.'],
            ['label' => 'Telephone number', 'value' => '+27 11 534 6557'],
            ['label' => 'Website', 'value' => 'www.nedbank.co.za'],
        ],
        [
            ['value' => 'The Management Company and Trustee are registered and approved under the Collective Investment Schemes Control Act (No. 45 of 2002). Prescient is a member of the Association for Savings and Investments South Africa.'],
        ],
        [
            ['label' => 'Investment Manager', 'value' => 'Foord Asset Management (Pty) Ltd. is an authorised Financial Services Provider (FSP578) under the Financial Advisory and Intermediary Services Act (No. 37 of 2002), to act in the capacity as investment manager. This information is not advice, as defined in the Financial Advisory and Intermediary Services Act (No. 37 of 2002). Please be advised that there may be representatives acting under supervision.'],
            ['label' => 'Registration number', 'value' => '1980/005495/07'],
            ['label' => 'Physical address', 'value' => '8 Forest Mews, 96 Forest Drive, Pinelands, 7405.'],
            ['label' => 'Telephone number', 'value' => '+27 (0)21 532 6999.'],
            ['label' => 'Website', 'value' => 'www.foord.co.za'],
        ],
    ],
];

$glossary = [
    'title' => 'GLOSSARY SUMMARY',
    'entries' => [
        ['term' => 'Annualised performance:', 'definition' => 'Annualised performance shows longer term performance rescaled to a 1-year period. Annualised performance is the average return per year over the period. Actual annual figures are available to the investor on request.'],
        ['term' => 'Highest and Lowest return:', 'definition' => 'The highest and lowest returns for any 1 year over the period since inception.'],
        ['term' => 'NAV:', 'definition' => 'The net asset value represents the assets of a Fund less its liabilities.'],
        ['term' => 'Alpha:', 'definition' => 'Denoted the outperformance of the fund over the benchmark.'],
        ['term' => 'Sharpe Ratio:', 'definition' => 'The Sharpe ratio is used to indicate the excess return the portfolio delivers over the risk free rate per unit of risk adopted by the fund.'],
        ['term' => 'Standard Deviation:', 'definition' => 'The deviation of the return stream relative to its own average.'],
        ['term' => 'Max Drawdown:', 'definition' => 'The maximum peak to trough loss suffered by the Fund since inception.'],
        ['term' => 'Max Gain:', 'definition' => 'Largest increase in any single month.'],
        ['term' => '% Positive Month:', 'definition' => 'The percentage of months since inception where the Fund has delivered positive return.'],
        ['term' => 'Average Duration:', 'definition' => 'The weighted average duration of all the underlying interest bearing instruments in the Fund.'],
        ['term' => 'Average Credit quality:', 'definition' => 'The weighted average credit quality of all the underlying interest bearing instruments in the Fund (internally calculated).'],
        ['term' => 'Fund Specific Risks:', 'definition' => ''],
        ['term' => 'Default risk:', 'definition' => 'The risk that the issuers of fixed income instruments (e.g. bonds) may not be able to meet interest payments nor repay the money they have borrowed. The issuers credit quality is vital. The worse the credit quality, the greater the risk of default and therefore investment loss.'],
        ['term' => 'Derivatives risk:', 'definition' => 'The use of derivatives could increase overall risk by magnifying the effect of both gains and losses in a Fund. As such, large changes in value and potentially large financial losses could result.'],
        ['term' => 'Developing Market (excluding SA) risk:', 'definition' => 'Some of the countries invested in may have less developed legal, political, economic and/or other systems. These markets carry a higher risk of financial loss than those in countries generally regarded as being more developed.'],
        ['term' => 'Foreign Investment risk:', 'definition' => 'Foreign securities investments may be subject to risks pertaining to overseas jurisdictions and markets, including (but not limited to) local liquidity, macroeconomic, political, tax, settlement risks and currency fluctuations.'],
        ['term' => 'Interest rate risk:', 'definition' => 'The value of fixed income investments (e.g. bonds) tends to be inversely related to interest and inflation rates. Hence their value decreases when interest rates and/or inflation rises.'],
        ['term' => 'Property risk:', 'definition' => 'Investments in real estate securities can carry the same risks as investing directly in real estate itself. Real estate prices move in response to a variety of factors, including local, regional and national economic and political conditions, interest rates and tax considerations.'],
        ['term' => 'Currency exchange risk:', 'definition' => 'Changes in the relative values of individual currencies may adversely affect the value of investments and any related income.'],
        ['term' => 'Geographic / Sector risk:', 'definition' => 'For investments primarily concentrated in specific countries, geographical regions and/or industry sectors, their resulting value may decrease whilst portfolios more broadly invested might grow.'],
        ['term' => 'Derivative counterparty risk:', 'definition' => 'A counterparty to a derivative transaction may experience a breakdown in meeting its obligations thereby leading to financial loss.'],
        // Reference quirk: this one term is NOT bold on the published sheet.
        ['term' => 'Liquidity risk:', 'bold' => false, 'definition' => 'If there are insufficient buyers or sellers of particular investments, the result may lead to delays in trading and being able to make settlements, and/or large fluctuations in value. This may lead to larger financial losses than expected.'],
        ['term' => 'Equity investment risk:', 'definition' => 'Value of equities (e.g. shares) and equity-related investments may vary according to company profits and future prospects as well as more general market factors. In the event of a company default (e.g. bankruptcy), the owners of their equity rank last in terms of any financial payment from that company.'],
    ],
];

$sharedPage2 = [
    'contributorsDetractors' => [
        'title' => 'CONTRIBUTORS/DETRACTORS',
        'rows' => [
            ['name' => 'Contributors to performance:', 'value' => 'Wharf Real Estate, Alibaba, Microsoft, EPAM Systems'],
            ['name' => 'Detractors from performance:', 'value' => 'APR Corp, NKT, Wheaton Precious Metals, Whitehaven Coal'],
        ],
    ],
    'policyObjective' => [
        'title' => 'POLICY OBJECTIVE',
        'text' => 'The portfolio has adhered to its policy objective.',
    ],
    'investingOffshore' => [
        'title' => 'INVESTING OFFSHORE',
        'text' => 'While an investment in the fund provides for global asset exposure, you may only invest and withdraw rands. Your contribution to a fund of this nature is over and above the South African offshore allowance.',
    ],
    'contactDetails' => $contactDetails,
    'glossary' => $glossary,
];

$sharedSidebar = [
    'domicile' => 'South Africa',
    'management_company' => 'Prescient Management Company (RF) (Pty) Ltd<br>Registration Number: 2002/022560/07',
    // The Class A reference misspells the manager as "Jing Cong Zue"; the
    // Class B2 and 809 sheets both read "Xue", so both classes carry Xue.
    // Flagged for the client.
    // The reference breaks the line after "and" rather than letting it wrap.
    'fund_managers' => 'Brian Arcese, Dave Foord and<br>Jing Cong Xue',
    'inception_date' => '21 February 2022',
    'base_currency' => 'South African rands',
    'equity_indicator_description' => 'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.',
    'category' => 'Global - Multi Asset - Flexible',
    'benchmark' => 'US headline CPI',
    'minimums' => 'R10 000 initial lump sum and R1 000 subsequent investments',
    'last_distributions' => 'The Foord International Fund, in which the fund invests, does not distribute its income.',
    'income_characteristics' => 'Marginal to zero income yield as the Foord International Fund is a roll-up fund and does not distribute its income.',
    'portfolio_orientation' => 'Fully invested in the Foord International Fund, sub-fund of Foord SICAV, domiciled in Luxembourg.',
    'significant_restrictions' => 'The portfolio may only invest in cash and one other collective investment scheme.',
    'risk_indicator' => 'Moderate.',
    'risk_indicator_definition' => 'These portfolios typically exhibit more volatility and potential for capital losses in the short-term due to higher exposure to equities and exposure to offshore markets where currency fluctuations may result in short-term capital losses.',
    'foreign_assets' => 'Investment returns in US$ may not reconcile exactly to those of Foord International Fund as pricing within the feeder fund lags by one valuation interval.',
];

// ---------------------------------------------------------------------------
// Per-class values (everything the two sheets disagree on, minus feed data)
// ---------------------------------------------------------------------------

$classes = [
    'A' => [
        'time_horizon' => 'Longer than three years.',
        'annual_fee' => '0.35% ex VAT',
    ],
    'B2' => [
        'time_horizon' => 'Longer than five years.',
        'annual_fee' => '0.10% ex VAT',
    ],
];

foreach ($classes as $classCode => $classData) {
    $fund = Fund::firstOrNew(['fund_code' => '822', 'class_code' => $classCode]);

    $fund->fill($sharedSidebar);

    $fund->user_id = $fund->user_id ?? $ownerId;
    $fund->name = 'PRESCIENT FOORD INTERNATIONAL FEEDER FUND — CLASS '.$classCode;
    $fund->class = $classCode;
    $fund->template = 'show-prescient-feeder';
    $fund->description = $description;
    $fund->logo_url = 'https://foord.co.za/themes/custom/mirum/logo.png';
    $fund->time_horizon = $classData['time_horizon'];

    $fund->important_info_title = 'IMPORTANT INFORMATION FOR INVESTORS';
    $fund->important_info_paragraphs = $importantInfoParagraphs;

    // Performance table: the import replaces `rows` every month, so only the
    // title/headers/columnKeys/footnotes are seeded here.
    $performanceTable = $fund->performance_table ?? [];
    $performanceTable['title'] = 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)';
    $performanceTable['headers'] = $performanceHeaders;
    $performanceTable['columnKeys'] = $performanceColumnKeys;
    $performanceTable['footnotes'] = $performanceFootnotes;
    $fund->performance_table = $performanceTable;

    // Asset allocation: the import replaces the rows but preserves an
    // existing title, so the reference's "(Effective exposure)" qualifier
    // is seeded here.
    $assetAllocation = $fund->asset_allocation ?? [];
    $assetAllocation['title'] = 'ASSET ALLOCATION % (Effective exposure)';
    $fund->asset_allocation = $assetAllocation;

    // Charts: the import replaces `performanceData`; the title is static.
    $chartData = $fund->chart_data ?? [];
    $chartData['title'] = 'ILLUSTRATIVE PERFORMANCE²';
    $fund->chart_data = $chartData;

    // Fee rates are static per class; the TIC table comes from the feed.
    $fees = $fund->fees ?? [];
    $fees['feeRates'] = [
        'title' => 'FEE RATES (CLASS '.$classCode.')',
        'rates' => [
            ['name' => 'Initial, exit and switching fees', 'value' => '0.0%'],
            ['name' => 'Annual fee', 'value' => $classData['annual_fee']],
        ],
        'globalFunds' => [
            'title' => 'Foord global funds:',
            'funds' => [
                ['name' => 'Foord International Fund', 'value' => '1.00% per annum fixed annual'],
            ],
        ],
    ];
    $fees['totalInvestmentCharge'] = array_merge($fees['totalInvestmentCharge'] ?? [], [
        'title' => 'TOTAL INVESTMENT CHARGE %',
        'headers' => ['', '12 MONTHS', '36 MONTHS'],
        'description' => 'The Fund’s TER reflects the percentage of the average Net Asset Value (NAV) of the portfolio that was incurred as charges, levies and fees related to the management of the portfolio. A higher TER does not necessarily imply a poor return, nor does a low TER imply a good return. The current TER cannot be regarded as an indication of future TER’s. During the phase in period TER’s do not include information gathered over a full year. Transaction costs (TC) is the percentage of the value of the Fund incurred as costs relating to the buying and selling of the Fund’s underlying assets. Transaction costs are a necessary cost in administering the Fund and impacts the Fund returns. It should not be considered in isolation as returns may be impacted by many other factors over time including market returns, the type of Fund, investment decisions of the investment manager and the TER.',
    ]);
    $fund->fees = $fees;

    $fund->page2_content = array_merge($fund->page2_content ?? [], $sharedPage2);

    $fund->save();

    echo 'Seeded fund '.$fund->id.' — 822 Class '.$classCode.PHP_EOL;
}
