<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Folder code on the monthly SFTP data feed; ignore(null) on store.
            // A code covers several share classes, so it is the (fund_code,
            // class_code) pair that must be unique — not the code alone.
            'fund_code' => [
                'nullable', 'string', 'max:10',
                Rule::unique('funds', 'fund_code')
                    ->where(fn ($query) => $query->where('class_code', $this->input('class_code')))
                    ->ignore($this->route('fund')),
            ],
            // Share class token in the data-feed filenames: A, B, B2, B3, R, R1
            'class_code' => ['nullable', 'string', 'max:6', 'regex:/^[A-Za-z][0-9]*$/'],
            // Fund metadata
            'template' => ['nullable', 'string', 'in:show,show-equity,show-flexible,show-conservative,show-bond,show-flex-income,show-income,show-inflation-income,show-domestic,show-absolute,show-shariah,show-shariah-income,show-international,show-international-trust,show-global-equity,show-feeder,show-prescient-feeder,show-prescient-global-equity,show-hassen-shariah,show-australian-feeder,show-asia-ex-japan'],
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
            'sector_allocation' => ['nullable', 'json'],
            'chart_description' => ['nullable', 'string'],
            'fees' => ['nullable', 'json'],
        ];
    }
}
