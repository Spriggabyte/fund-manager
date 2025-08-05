<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
        }
        
        /* Header Section */
        .header {
            background-color: #1F2937;
            color: white;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .header-content h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .date-badge {
            background-color: #dc2626;
            color: white;
            padding: 8px 16px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 16px;
        }
        
        .fund-description {
            color: #D1D5DB;
            font-size: 11px;
            max-width: 600px;
            margin-top: 8px;
        }
        
        .logo {
            height: 48px;
            margin-left: 20px;
        }
        
        /* Main Layout */
        .main-layout {
            width: 100%;
            table-layout: fixed;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: #F3F4F6;
            padding: 20px;
            font-size: 11px;
            vertical-align: top;
        }
        
        .sidebar-item {
            margin-bottom: 16px;
        }
        
        .sidebar-item h3 {
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .sidebar-item p {
            color: #000;
            line-height: 1.3;
        }
        
        .equity-indicator {
            background: linear-gradient(to right, #dc2626 0%, #dc2626 75%, #e5e7eb 75%, #e5e7eb 100%);
            height: 16px;
            border-radius: 2px;
            margin-bottom: 8px;
        }
        
        /* Main Content */
        .main-content {
            padding: 20px 24px;
            vertical-align: top;
        }
        
        .section {
            margin-bottom: 32px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #000;
        }
        
        .section-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        th {
            background-color: #E5E7EB;
            border: 1px solid #D1D5DB;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
        }
        
        th:first-child {
            text-align: left;
        }
        
        th:not(:first-child) {
            text-align: center;
        }
        
        td {
            border: 1px solid #D1D5DB;
            padding: 8px 12px;
            font-size: 11px;
        }
        
        td:first-child {
            text-align: left;
        }
        
        td:not(:first-child) {
            text-align: center;
        }
        
        tr:nth-child(odd) {
            background-color: #F9FAFB;
        }
        
        .total-row {
            background-color: #DC2626 !important;
            color: white;
            font-weight: bold;
        }
        
        /* Charts */
        .charts-grid {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 32px;
        }
        
        .chart-container {
            width: 48%;
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 16px;
            vertical-align: top;
        }
        
        .chart-container .section-title {
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .chart-placeholder {
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 14px;
            border-radius: 4px;
        }
        
        .footnotes {
            font-size: 10px;
            color: #666;
            margin-top: 8px;
            line-height: 1.3;
        }
        
        /* Performance Table Specific */
        .performance-table {
            font-size: 10px;
        }
        
        .performance-table th,
        .performance-table td {
            padding: 6px 8px;
        }
        
        .performance-footnotes {
            font-size: 9px;
            color: #666;
            margin-top: 8px;
        }
        
        .performance-footnotes p {
            margin-bottom: 4px;
        }
        
        /* Page 3 Styles */
        .page-3 {
            background: white;
            margin-top: 32px;
            width: 100%;
            table-layout: fixed;
        }
        
        .important-info-sidebar {
            width: 280px;
            background-color: #1F2937;
            color: white;
            padding: 20px;
            font-size: 10px;
            vertical-align: top;
        }
        
        .important-info-sidebar h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        
        .important-info-sidebar p {
            color: #D1D5DB;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .fees-content {
            padding: 20px 24px;
            vertical-align: top;
        }
        
        .fee-table-wrapper {
            background-color: #f3f4f6;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .fee-table-wrapper table {
            margin-bottom: 0;
        }
        
        .fee-table-wrapper th,
        .fee-table-wrapper td {
            border: none;
            padding: 8px 0;
        }
        
        .fee-table-wrapper td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        /* Footer */
        .footer-info {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
            color: #666;
        }
        
        .footer-info p {
            margin-bottom: 8px;
        }
        
        .contact-info {
            margin: 16px 0;
        }
        
        .footer-logo {
            height: 40px;
            margin-top: 16px;
        }
        
        /* Page breaks */
        .page-break {
            page-break-before: always;
        }
        
        @page {
            margin: 15mm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page 1 - Main Fund Information -->
        <div class="header">
            <div class="header-content">
                <div class="date-badge">{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}</div>
                <h1>{{ $fund->data['fund']['name'] ?? $fund->name }}</h1>
                <div class="fund-description">{{ $fund->data['fund']['description'] ?? '' }}</div>
            </div>
            <div>
                <img src="{{ $fund->data['fund']['logoUrl'] ?? 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 200 80\'%3E%3Ctext x=\'10\' y=\'50\' font-family=\'Arial\' font-size=\'40\' font-weight=\'bold\' fill=\'white\'%3EFOORD%3C/text%3E%3Ccircle cx=\'170\' cy=\'40\' r=\'25\' fill=\'%23f97316\'/%3E%3C/svg%3E' }}" alt="Foord Logo" class="logo">
            </div>
        </div>

        <table class="main-layout">
            <tr>
                <!-- Sidebar -->
                <td class="sidebar">
                    @if(isset($fund->data['sidebar']))
                        @foreach ($fund->data['sidebar'] as $key => $value)
                        <div class="sidebar-item">
                            <h3>{{ strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                            @if (is_array($value))
                                <div class="equity-indicator"></div>
                                <p>{{ $value['description'] ?? '' }}</p>
                            @else
                                <p>{!! $value !!}</p>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </td>

                <!-- Main Content -->
                <td class="main-content">
                <!-- Asset Allocation -->
                @if(isset($fund->data['mainContent']['assetAllocation']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['mainContent']['assetAllocation']['title'] }}</div>
                    <div class="section-subtitle">{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}</div>
                    <table>
                        <thead>
                            <tr>
                                @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $header)
                                    <th>{!! $header !!}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['sa'] }}</td>
                                <td>{{ $row['foreign'] }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['change'] }}</td>
                            </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>{{ $fund->data['mainContent']['assetAllocation']['total']['name'] }}</td>
                                <td>{{ $fund->data['mainContent']['assetAllocation']['total']['sa'] }}</td>
                                <td>{{ $fund->data['mainContent']['assetAllocation']['total']['foreign'] }}</td>
                                <td>{{ $fund->data['mainContent']['assetAllocation']['total']['total'] }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Top 10 Investments -->
                @if(isset($fund->data['mainContent']['topInvestments']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['mainContent']['topInvestments']['title'] }}</div>
                    <table>
                        <thead>
                            <tr>
                                @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $row)
                            <tr>
                                <td>{{ $row['security'] }}</td>
                                <td>{{ $row['assetClass'] }}</td>
                                <td>{{ $row['market'] }}</td>
                                <td>{{ $row['percentage'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Charts -->
                @if(isset($fund->data['mainContent']['charts']))
                <div class="section">
                    <table class="charts-grid">
                        <tr>
                            <td class="chart-container">
                                <div class="section-title">INVESTMENT STRATEGY VS SA INFLATION</div>
                                <div style="width: 100%; height: 200px; border: 1px solid #e0e0e0; background: #f8f9fa; text-align: center; padding: 40px 20px;">
                                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 20px;">Performance Chart</div>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="border: none; padding: 8px; background: #DC2626; color: white; width: 60%;">Fund Performance</td>
                                            <td style="border: none; padding: 8px; background: #ffffff; color: #DC2626; font-weight: bold;">15.2%</td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 8px; background: #6B7280; color: white; width: 40%;">Benchmark</td>
                                            <td style="border: none; padding: 8px; background: #ffffff; color: #6B7280; font-weight: bold;">8.5%</td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 8px; background: #9CA3AF; color: white; width: 30%;">Inflation</td>
                                            <td style="border: none; padding: 8px; background: #ffffff; color: #9CA3AF; font-weight: bold;">5.0%</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="footnotes">
                                    {{ $fund->data['mainContent']['charts']['investmentStrategy']['description'] ?? '' }}
                                </div>
                            </td>
                            <td style="width: 4%;"></td>
                            <td class="chart-container">
                                <div class="section-title">PORTFOLIO PERFORMANCE VS BENCHMARK</div>
                                <div style="width: 100%; height: 200px; border: 1px solid #e0e0e0; background: #f8f9fa; text-align: center; padding: 40px 20px;">
                                    <div style="font-size: 14px; font-weight: bold; margin-bottom: 20px;">Portfolio Growth</div>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="border: none; padding: 8px; background: #DC2626; color: white; width: 80%;">Fund</td>
                                            <td style="border: none; padding: 8px; background: #ffffff; color: #DC2626; font-weight: bold;">R 2,487</td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 8px; background: #1F2937; color: white; width: 70%;">Benchmark</td>
                                            <td style="border: none; padding: 8px; background: #ffffff; color: #1F2937; font-weight: bold;">R 2,222</td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                @endif

                <!-- Performance Table -->
                @if(isset($fund->data['mainContent']['performanceTable']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['mainContent']['performanceTable']['title'] }}</div>
                    <table class="performance-table">
                        <thead>
                            <tr>
                                @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $header)
                                    <th>{!! $header !!}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $row)
                            <tr>
                                <td>{!! $row['name'] !!}</td>
                                <td>{{ $row['cashValue'] }}</td>
                                <td>{{ $row['sinceInception'] }}</td>
                                <td>{{ $row['15yrs'] }}</td>
                                <td>{{ $row['10yrs'] }}</td>
                                <td>{{ $row['7yrs'] }}</td>
                                <td>{{ $row['5yrs'] }}</td>
                                <td>{{ $row['3yrs'] }}</td>
                                <td>{{ $row['1yr'] }}</td>
                                <td>{{ $row['thisMonth'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="performance-footnotes">
                        @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $footnote !!}</p>
                            @endforeach
                        @endif
                    </div>
                </div>
                @endif
                </td>
            </tr>
        </table>

        <!-- Page 2 - Fees and Important Information -->
        <table class="page-3 page-break">
            <tr>
                <!-- Important Information Sidebar -->
                @if(isset($fund->data['importantInfo']))
                <td class="important-info-sidebar">
                    <h2>{{ $fund->data['importantInfo']['title'] }}</h2>
                    @foreach ($fund->data['importantInfo']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    <p style="margin-top: 16px;">{{ $fund->data['importantInfo']['publishedDate'] }}</p>
                </td>
                @endif

                <!-- Fees Content -->
                <td class="fees-content">
                <!-- Fee Rates -->
                @if(isset($fund->data['fees']['feeRates']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['fees']['feeRates']['title'] }}</div>
                    <div class="fee-table-wrapper">
                        <table>
                            @foreach ($fund->data['fees']['feeRates']['rates'] as $rate)
                            <tr>
                                <td>{{ $rate['name'] }}</td>
                                <td>{{ $rate['value'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="padding-top: 16px; font-weight: bold;">{{ $fund->data['fees']['feeRates']['globalFunds']['title'] }}</td>
                                <td></td>
                            </tr>
                            @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $fund_item)
                            <tr>
                                <td>{{ $fund_item['name'] }}</td>
                                <td>{{ $fund_item['value'] }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    <p style="font-size: 10px; color: #666; margin-top: 8px;">{{ $fund->data['fees']['feeRates']['description'] }}</p>
                </div>
                @endif

                <!-- Total Investment Charge -->
                @if(isset($fund->data['fees']['totalInvestmentCharge']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}</div>
                    <table>
                        <thead>
                            <tr>
                                @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['12m'] }}</td>
                                <td>{{ $row['36m'] }}</td>
                            </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] }}</td>
                                <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['12m'] }}</td>
                                <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['36m'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="font-size: 10px; color: #666; margin-top: 16px;">{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}</p>
                </div>
                @endif

                <!-- Performance Fees -->
                @if(isset($fund->data['fees']['performanceFees']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['fees']['performanceFees']['title'] }}</div>
                    @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                        <p style="font-size: 11px; color: #666; margin-bottom: 16px;">{{ $paragraph }}</p>
                    @endforeach
                </div>
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['fees']['performanceFeeExamples']['title'] }}</div>
                    <table>
                        <thead>
                            <tr>
                                @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $header)
                                    <th>{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['a'] }}</td>
                                <td>{{ $row['b'] }}</td>
                                <td>{{ $row['c'] }}</td>
                                <td>{{ $row['d'] }}</td>
                            </tr>
                            @endforeach
                            <tr class="total-row">
                                <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] }}</td>
                                <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['a'] }}</td>
                                <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['b'] }}</td>
                                <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['c'] }}</td>
                                <td>{!! $fund->data['fees']['performanceFeeExamples']['total']['d'] !!}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="font-size: 10px; color: #666; margin-top: 8px;">{{ $fund->data['fees']['performanceFeeExamples']['footnote'] }}</p>
                </div>
                @endif

                <!-- Footer Information -->
                @if(isset($fund->data['footer']))
                <div class="footer-info">
                    <p>{{ $fund->data['footer']['info'] }}</p>
                    <p style="margin-bottom: 24px;">{{ $fund->data['footer']['freeOfCharge'] }}</p>
                    <div class="contact-info">
                        <p>T. {{ $fund->data['footer']['contact']['phone'] }}</p>
                        <p>E. {{ $fund->data['footer']['contact']['email'] }}</p>
                        <p>{{ $fund->data['footer']['contact']['website'] }}</p>
                    </div>
                    <img src="{{ $fund->data['footer']['logoUrl'] }}" alt="Foord Logo" class="footer-logo">
                </div>
                @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>