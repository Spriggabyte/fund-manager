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
