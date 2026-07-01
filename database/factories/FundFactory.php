<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
class FundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'class' => fake()->randomElement(['A', 'B', 'C', null]),
            'user_id' => User::factory(),
        ];
    }

    /**
     * A fully-populated fund exercising every fact-sheet section (header,
     * sidebar, asset allocation, top investments, performance table, both
     * charts, important info). Keys match what pdf.blade.php reads.
     */
    public function representative(): static
    {
        return $this->withData([
            'fund' => [
                'name' => 'Foord Balanced Fund',
                'date' => '30 June 2026',
                'template' => 'show',
                'description' => 'A managed fund seeking inflation-beating returns over the long term.',
            ],
            'sidebar' => [
                'category' => 'South African MA High Equity',
                'benchmark' => 'CPI + 5%',
                'unitPrice' => '12.3456',
            ],
            'mainContent' => [
                'assetAllocation' => [
                    'title' => 'ASSET ALLOCATION',
                    'headers' => ['Asset class', 'Total'],
                    'rows' => [
                        ['name' => 'Equity', 'total' => 65],
                        ['name' => 'Bonds', 'total' => 20],
                        ['name' => 'Cash', 'total' => 15],
                    ],
                ],
                'topInvestments' => [
                    'title' => 'TOP 10 INVESTMENTS',
                    'headers' => ['Security', 'Asset class', 'Market', '%'],
                    'rows' => [
                        ['security' => 'Naspers', 'assetClass' => 'Equity', 'market' => 'SA', 'percentage' => 5.2],
                        ['security' => 'Prosus', 'assetClass' => 'Equity', 'market' => 'Foreign', 'percentage' => 4.1],
                    ],
                ],
                'performanceTable' => [
                    'title' => 'PORTFOLIO PERFORMANCE',
                    'headers' => ['', '1 YR', '5 YRS'],
                    'rows' => [
                        ['name' => 'Foord Balanced Fund', '1yr' => 12.3, '5yrs' => 10.1],
                        ['name' => 'Benchmark', '1yr' => 9.8, '5yrs' => 8.4],
                    ],
                ],
                'charts' => [
                    'inflationData' => [
                        ['date' => '2020-12', 'inflation' => 4.2, 'hurdle' => 5, 'excess' => 3.1, 'composite' => 12.3],
                        ['date' => '2021-12', 'inflation' => 5.0, 'hurdle' => 5, 'excess' => 2.0, 'composite' => 12.0],
                    ],
                    'portfolioData' => [
                        ['date' => '2020-12', 'fund' => 100, 'benchmark' => 100],
                        ['date' => '2021-12', 'fund' => 112, 'benchmark' => 108],
                    ],
                ],
            ],
            'importantInfo' => [
                'title' => 'IMPORTANT INFORMATION FOR INVESTORS',
                'paragraphs' => ['Collective investment schemes are generally medium- to long-term investments.'],
                'publishedDate' => 'Published 30 June 2026',
            ],
        ]);
    }

    public function withData(array $data = []): static
    {
        $attributes = [];

        // Map legacy data structure to new columns
        if (isset($data['fund'])) {
            $attributes['fund_date'] = $data['fund']['date'] ?? null;
            $attributes['description'] = $data['fund']['description'] ?? null;
            $attributes['logo_url'] = $data['fund']['logoUrl'] ?? null;
            $attributes['template'] = $data['fund']['template'] ?? null;
            if (isset($data['fund']['name'])) {
                $attributes['name'] = $data['fund']['name'];
            }
        }

        if (isset($data['sidebar'])) {
            $sidebar = $data['sidebar'];
            $attributes['category'] = $sidebar['category'] ?? null;
            $attributes['benchmark'] = $sidebar['benchmark'] ?? null;
            $attributes['unit_price'] = $sidebar['unitPrice'] ?? null;
        }

        if (isset($data['mainContent'])) {
            $mc = $data['mainContent'];
            $attributes['asset_allocation'] = $mc['assetAllocation'] ?? null;
            $attributes['top_investments'] = $mc['topInvestments'] ?? null;
            $attributes['performance_table'] = $mc['performanceTable'] ?? null;
            $attributes['chart_data'] = $mc['charts'] ?? null;
        }

        if (isset($data['fees'])) {
            $attributes['fees'] = $data['fees'];
        }

        if (isset($data['importantInfo'])) {
            $attributes['important_info_title'] = $data['importantInfo']['title'] ?? null;
            $attributes['important_info_paragraphs'] = $data['importantInfo']['paragraphs'] ?? null;
            $attributes['important_info_published_date'] = $data['importantInfo']['publishedDate'] ?? null;
        }

        return $this->state($attributes);
    }
}
