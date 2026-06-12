<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:255'],
            // Fund metadata
            'template' => ['nullable', 'string', 'in:show,show-equity,show-flexible,show-international'],
            'fund_date' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            // Sidebar
            'category' => ['nullable', 'string', 'max:255'],
            'domicile' => ['nullable', 'string', 'max:255'],
            'minimums' => ['nullable', 'string', 'max:255'],
            'benchmark' => ['nullable', 'string'],
            'unit_price' => ['nullable', 'string', 'max:255'],
            'isin_number' => ['nullable', 'string', 'max:255'],
            'sedol' => ['nullable', 'string', 'max:255'],
            'risk_of_loss' => ['nullable', 'string'],
            'time_horizon' => ['nullable', 'string', 'max:255'],
            'base_currency' => ['nullable', 'string', 'max:255'],
            'fund_managers' => ['nullable', 'string'],
            'foreign_assets' => ['nullable', 'string'],
            'inception_date' => ['nullable', 'string', 'max:255'],
            'number_of_units' => ['nullable', 'string', 'max:255'],
            'portfolio_size' => ['nullable', 'string', 'max:255'],
            'equity_indicator_description' => ['nullable', 'string'],
            'last_distributions' => ['nullable', 'string'],
            'management_company' => ['nullable', 'string'],
            'income_distributions' => ['nullable', 'string'],
            'portfolio_orientation' => ['nullable', 'string'],
            'income_characteristics' => ['nullable', 'string'],
            'significant_restrictions' => ['nullable', 'string'],
            // Footer
            'footer_info' => ['nullable', 'string'],
            'footer_email' => ['nullable', 'string', 'max:255'],
            'footer_phone' => ['nullable', 'string', 'max:255'],
            'footer_website' => ['nullable', 'string', 'max:255'],
            'footer_logo_url' => ['nullable', 'string'],
            'footer_free_of_charge' => ['nullable', 'string', 'max:255'],
            // Important info
            'important_info_title' => ['nullable', 'string', 'max:255'],
            'important_info_published_date' => ['nullable', 'string', 'max:255'],
            'important_info_paragraphs' => ['nullable', 'json'],
            // JSON data
            'asset_allocation' => ['nullable', 'json'],
            'top_investments' => ['nullable', 'json'],
            'performance_table' => ['nullable', 'json'],
            'chart_data' => ['nullable', 'json'],
            'fees' => ['nullable', 'json'],
        ];
    }
}
