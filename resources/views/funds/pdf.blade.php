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
        }
        
        .header {
            background-color: #374151;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }
        
        .date-badge {
            background-color: #dc2626;
            color: white;
            padding: 8px 16px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 16px;
        }
        
        .fund-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .fund-description {
            color: #d1d5db;
            font-size: 11px;
            max-width: 400px;
        }
        
        .logo {
            height: 50px;
            width: auto;
        }
        
        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
        }
        
        .sidebar {
            width: 250px;
            background-color: #f3f4f6;
            padding: 20px;
            font-size: 10px;
            float: left;
        }
        
        .sidebar h3 {
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .sidebar-item {
            margin-bottom: 16px;
        }
        
        .equity-indicator {
            background: linear-gradient(to right, #dc2626 0%, #dc2626 75%, #e5e7eb 75%, #e5e7eb 100%);
            height: 20px;
            border-radius: 3px;
            margin-bottom: 8px;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }
        
        .section {
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        
        .section-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }
        
        td:not(:first-child) {
            text-align: center;
        }
        
        .total-row {
            background-color: #dc2626;
            color: white;
            font-weight: bold;
        }
        
        .chart-placeholder {
            background-color: #f3f4f6;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
        }
        
        .charts-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .chart-container {
            flex: 1;
        }
        
        .footnotes {
            font-size: 9px;
            color: #6b7280;
            margin-top: 8px;
        }
        
        .footnotes p {
            margin-bottom: 4px;
        }
        
        /* Page 2 styles */
        .page-break {
            page-break-before: always;
        }
        
        .important-info {
            background-color: #374151;
            color: white;
            padding: 20px;
            width: 250px;
            float: left;
            font-size: 9px;
        }
        
        .important-info h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        
        .important-info p {
            color: #d1d5db;
            margin-bottom: 12px;
        }
        
        .fees-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .fee-table-wrapper {
            background-color: #f3f4f6;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .footer-info {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
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
        
        /* Ensure content fits on page */
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Page 1 -->
        <div class="header">
            <div>
                <div class="date-badge">{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}</div>
                <div class="fund-title">{{ $fund->data['fund']['name'] ?? $fund->name }}</div>
                <div class="fund-description">
                    {{ $fund->data['fund']['description'] ?? '' }}
                </div>
            </div>
            <div>FOORD</div>
        </div>

        <div class="content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar">
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
            </div>

            <!-- Main Content -->
            <div class="main-content">
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
                <div class="charts-grid">
                    <div class="chart-container">
                        <div class="section-title">{{ $fund->data['mainContent']['charts']['investmentStrategy']['title'] }}</div>
                        <div class="chart-placeholder">
                            {{ $fund->data['mainContent']['charts']['investmentStrategy']['chartPlaceholder'] }}
                        </div>
                        <div class="footnotes">
                            {{ $fund->data['mainContent']['charts']['investmentStrategy']['description'] }}
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="section-title">{{ $fund->data['mainContent']['charts']['portfolioPerformance']['title'] }}</div>
                        <div class="chart-placeholder">
                            {{ $fund->data['mainContent']['charts']['portfolioPerformance']['chartPlaceholder'] }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Performance Table -->
                @if(isset($fund->data['mainContent']['performanceTable']))
                <div class="section">
                    <div class="section-title">{{ $fund->data['mainContent']['performanceTable']['title'] }}</div>
                    <table>
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
                    <div class="footnotes">
                        @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $footnote !!}</p>
                            @endforeach
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Page 2 -->
        <div class="page-break">
            <div class="content-wrapper">
                <!-- Important Information Sidebar -->
                @if(isset($fund->data['importantInfo']))
                <div class="important-info">
                    <h2>{{ $fund->data['importantInfo']['title'] }}</h2>
                    @foreach ($fund->data['importantInfo']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    <p style="margin-top: 16px;">{{ $fund->data['importantInfo']['publishedDate'] }}</p>
                </div>
                @endif

                <!-- Fees Content -->
                <div class="fees-content">
                    <!-- Fee Rates -->
                    @if(isset($fund->data['fees']['feeRates']))
                    <div class="section">
                        <div class="section-title">{{ $fund->data['fees']['feeRates']['title'] }}</div>
                        <div class="fee-table-wrapper">
                            <table style="border: none;">
                                @foreach ($fund->data['fees']['feeRates']['rates'] as $rate)
                                <tr>
                                    <td style="border: none; padding: 4px;">{{ $rate['name'] }}</td>
                                    <td style="border: none; padding: 4px; text-align: right;">{{ $rate['value'] }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td style="border: none; padding: 4px; padding-top: 16px; font-weight: bold;">{{ $fund->data['fees']['feeRates']['globalFunds']['title'] }}</td>
                                    <td style="border: none;"></td>
                                </tr>
                                @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $fund_item)
                                <tr>
                                    <td style="border: none; padding: 4px;">{{ $fund_item['name'] }}</td>
                                    <td style="border: none; padding: 4px; text-align: right;">{{ $fund_item['value'] }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div class="footnotes">{{ $fund->data['fees']['feeRates']['description'] }}</div>
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
                        <div class="footnotes">{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}</div>
                    </div>
                    @endif
                    
                    <!-- Performance Fees -->
                    @if(isset($fund->data['fees']['performanceFees']))
                    <div class="section">
                        <div class="section-title">{{ $fund->data['fees']['performanceFees']['title'] }}</div>
                        @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                            <p style="margin-bottom: 12px; font-size: 10px; color: #6b7280;">{{ $paragraph }}</p>
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
                        <div class="footnotes">{{ $fund->data['fees']['performanceFeeExamples']['footnote'] }}</div>
                    </div>
                    @endif

                    <!-- Footer Information -->
                    @if(isset($fund->data['footer']))
                    <div class="footer-info">
                        <p>{{ $fund->data['footer']['info'] }}</p>
                        <p>{{ $fund->data['footer']['freeOfCharge'] }}</p>
                        <div class="contact-info">
                            <p>T. {{ $fund->data['footer']['contact']['phone'] }}</p>
                            <p>E. {{ $fund->data['footer']['contact']['email'] }}</p>
                            <p>{{ $fund->data['footer']['contact']['website'] }}</p>
                        </div>
                        <div>FOORD</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>