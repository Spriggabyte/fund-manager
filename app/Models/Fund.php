<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class',
        // Folder code on the monthly SFTP data feed (YYYY-MM/{fund_code}/)
        'fund_code',
        // Share class within that folder (A, B2, B3, R) — selects which of the
        // folder's exports belong to this fund: {fund_code}{class_code}_*.xlsx
        'class_code',
        // Fund metadata
        'template',
        'fund_date',
        'description',
        'logo_url',
        // Sidebar fields
        'category',
        'domicile',
        'minimums',
        'benchmark',
        'unit_price',
        'isin_number',
        'sedol',
        'risk_of_loss',
        'time_horizon',
        'base_currency',
        'fund_managers',
        'foreign_assets',
        'inception_date',
        'number_of_units',
        'portfolio_size',
        'equity_indicator_description',
        'last_distributions',
        'management_company',
        'income_distributions',
        'portfolio_orientation',
        'income_characteristics',
        'significant_restrictions',
        // International (Luxembourg) sidebar fields
        'depository',
        'investment_manager',
        'sub_investment_manager',
        'type_of_shares',
        'fees_summary',
        'lipper_award',
        'page2_content',
        // Footer
        'footer_info',
        'footer_email',
        'footer_phone',
        'footer_website',
        'footer_logo_url',
        'footer_free_of_charge',
        // Important info
        'important_info_title',
        'important_info_published_date',
        'important_info_paragraphs',
        // Complex structured data (JSON)
        'asset_allocation',
        'top_investments',
        'performance_table',
        'chart_data',
        'sector_allocation',
        'chart_description',
        'fees',
    ];

    protected $casts = [
        'lipper_award' => 'array',
        'page2_content' => 'array',
        'important_info_paragraphs' => 'array',
        'asset_allocation' => 'array',
        'top_investments' => 'array',
        'performance_table' => 'array',
        'chart_data' => 'array',
        'sector_allocation' => 'array',
        'fees' => 'array',
    ];

    /**
     * Map from old JSON dot-notation paths to database columns.
     * Used by updateData() for inline editing and template backward compatibility.
     */
    public const FIELD_MAP = [
        'fund.name' => 'name',
        'fund.date' => 'fund_date',
        'fund.description' => 'description',
        'fund.logoUrl' => 'logo_url',
        'fund.template' => 'template',
        'sidebar.category' => 'category',
        'sidebar.domicile' => 'domicile',
        'sidebar.minimums' => 'minimums',
        'sidebar.benchmark' => 'benchmark',
        'sidebar.unitPrice' => 'unit_price',
        'sidebar.isinNumber' => 'isin_number',
        'sidebar.sedol' => 'sedol',
        'sidebar.riskOfLoss' => 'risk_of_loss',
        'sidebar.timeHorizon' => 'time_horizon',
        'sidebar.baseCurrency' => 'base_currency',
        'sidebar.fundManagers' => 'fund_managers',
        'sidebar.foreignAssets' => 'foreign_assets',
        'sidebar.inceptionDate' => 'inception_date',
        'sidebar.numberOfUnits' => 'number_of_units',
        'sidebar.portfolioSize' => 'portfolio_size',
        'sidebar.equityIndicator.description' => 'equity_indicator_description',
        'sidebar.lastDistributions' => 'last_distributions',
        'sidebar.managementCompany' => 'management_company',
        'sidebar.incomeDistributions' => 'income_distributions',
        'sidebar.portfolioOrientation' => 'portfolio_orientation',
        'sidebar.incomeCharacteristics' => 'income_characteristics',
        'sidebar.significantRestrictions' => 'significant_restrictions',
        'sidebar.depository' => 'depository',
        'sidebar.investmentManager' => 'investment_manager',
        'sidebar.subInvestmentManager' => 'sub_investment_manager',
        'sidebar.typeOfShares' => 'type_of_shares',
        'sidebar.fees' => 'fees_summary',
        // International templates label existing columns differently
        'sidebar.morningstarCategory' => 'category',
        'sidebar.initialInvestmentAmount' => 'minimums',
        'sidebar.totalFundSize' => 'portfolio_size',
        'sidebar.monthEndSharePrice' => 'unit_price',
        'sidebar.numberOfShares' => 'number_of_units',
        'footer.info' => 'footer_info',
        'footer.contact.email' => 'footer_email',
        'footer.contact.phone' => 'footer_phone',
        'footer.contact.website' => 'footer_website',
        'footer.logoUrl' => 'footer_logo_url',
        'footer.freeOfCharge' => 'footer_free_of_charge',
        'importantInfo.title' => 'important_info_title',
        'importantInfo.publishedDate' => 'important_info_published_date',
        'mainContent.chartDescription' => 'chart_description',
    ];

    /**
     * Map from dot-notation prefixes to JSON column names.
     */
    public const JSON_COLUMN_MAP = [
        'mainContent.assetAllocation' => 'asset_allocation',
        'mainContent.topInvestments' => 'top_investments',
        'mainContent.performanceTable' => 'performance_table',
        'mainContent.charts' => 'chart_data',
        'mainContent.sectorAllocation' => 'sector_allocation',
        'fees' => 'fees',
        'page2Content' => 'page2_content',
        'importantInfo.paragraphs' => 'important_info_paragraphs',
    ];

    /**
     * Build the legacy data array from the new columns.
     * This allows templates to continue using $fund->data['sidebar']['category'] etc.
     */
    public function getDataAttribute(): array
    {
        return [
            'fund' => [
                'name' => $this->attributes['name'] ?? '',
                'date' => $this->fund_date ?? '',
                'description' => $this->description ?? '',
                'logoUrl' => $this->logo_url ?? '',
                'template' => $this->template ?? 'show',
            ],
            'sidebar' => array_filter([
                'equityIndicator' => $this->equity_indicator_description ? [
                    'description' => $this->equity_indicator_description,
                ] : null,
                'category' => $this->category,
                'domicile' => $this->domicile,
                'minimums' => $this->minimums,
                'benchmark' => $this->benchmark,
                'unitPrice' => $this->unit_price,
                'isinNumber' => $this->isin_number,
                'sedol' => $this->sedol,
                'riskOfLoss' => $this->risk_of_loss,
                'timeHorizon' => $this->time_horizon,
                'baseCurrency' => $this->base_currency,
                'fundManagers' => $this->fund_managers,
                'foreignAssets' => $this->foreign_assets,
                'inceptionDate' => $this->inception_date,
                'numberOfUnits' => $this->number_of_units,
                'portfolioSize' => $this->portfolio_size,
                'lastDistributions' => $this->last_distributions,
                'managementCompany' => $this->management_company,
                'incomeDistributions' => $this->income_distributions,
                'portfolioOrientation' => $this->portfolio_orientation,
                'incomeCharacteristics' => $this->income_characteristics,
                'significantRestrictions' => $this->significant_restrictions,
                // International (Luxembourg) fact-sheet sidebar. The aliases
                // re-expose existing columns under the keys the international
                // templates label differently (MORNINGSTAR CATEGORY, …).
                'marketingCommunication' => ($this->template === 'show-international') ? true : null,
                'depository' => $this->depository,
                'investmentManager' => $this->investment_manager,
                'subInvestmentManager' => $this->sub_investment_manager,
                'typeOfShares' => $this->type_of_shares,
                'fees' => $this->fees_summary,
                'lipperAward' => $this->lipper_award,
                'morningstarCategory' => ($this->template === 'show-international') ? $this->category : null,
                'initialInvestmentAmount' => ($this->template === 'show-international') ? $this->minimums : null,
                'totalFundSize' => ($this->template === 'show-international') ? $this->portfolio_size : null,
                'monthEndSharePrice' => ($this->template === 'show-international') ? $this->unit_price : null,
                'numberOfShares' => ($this->template === 'show-international') ? $this->number_of_units : null,
            ], fn ($v) => $v !== null),
            'mainContent' => [
                'assetAllocation' => $this->asset_allocation,
                'topInvestments' => $this->top_investments,
                'performanceTable' => $this->performance_table,
                'charts' => $this->chart_data,
                'sectorAllocation' => $this->sector_allocation,
                'chartDescription' => $this->chart_description,
            ],
            'fees' => $this->fees,
            'page2Content' => $this->page2_content,
            'footer' => [
                'info' => $this->footer_info ?? '',
                'contact' => [
                    'email' => $this->footer_email ?? '',
                    'phone' => $this->footer_phone ?? '',
                    'website' => $this->footer_website ?? '',
                ],
                'logoUrl' => $this->footer_logo_url ?? '',
                'freeOfCharge' => $this->footer_free_of_charge ?? '',
            ],
            'importantInfo' => [
                'title' => $this->important_info_title ?? '',
                'paragraphs' => $this->important_info_paragraphs ?? [],
                'publishedDate' => $this->important_info_published_date ?? '',
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FundRevision::class)->orderBy('created_at', 'desc');
    }

    public function createRevision(?string $changedField = null, $oldValue = null, $newValue = null, ?string $changeSummary = null): FundRevision
    {
        // Falls back to the fund's owner when no user is authenticated
        // (artisan imports and other CLI contexts).
        return $this->revisions()->create([
            'user_id' => auth()->id() ?? $this->user_id,
            'name' => $this->name,
            'class' => $this->class,
            'data' => $this->data,
            'change_summary' => $changeSummary,
            'changed_field' => $changedField,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
        ]);
    }

    /**
     * Restore fund state from a revision's data snapshot.
     */
    public function restoreFromData(array $data): void
    {
        // Scalar fields from FIELD_MAP
        foreach (self::FIELD_MAP as $path => $column) {
            $value = $this->getNestedValue($data, $path);
            if ($value !== null) {
                $this->{$column} = $value;
            }
        }

        // JSON columns
        $this->asset_allocation = $data['mainContent']['assetAllocation'] ?? null;
        $this->top_investments = $data['mainContent']['topInvestments'] ?? null;
        $this->performance_table = $data['mainContent']['performanceTable'] ?? null;
        $this->chart_data = $data['mainContent']['charts'] ?? null;
        $this->sector_allocation = $data['mainContent']['sectorAllocation'] ?? null;
        $this->fees = $data['fees'] ?? null;
        $this->lipper_award = $data['sidebar']['lipperAward'] ?? null;
        $this->page2_content = $data['page2Content'] ?? null;
        $this->important_info_paragraphs = $data['importantInfo']['paragraphs'] ?? null;
    }

    /**
     * Get a value from the fund using dot notation (legacy data path or column name).
     */
    public function getDataValue(string $path): mixed
    {
        // Check if it maps to a direct column
        if (isset(self::FIELD_MAP[$path])) {
            return $this->{self::FIELD_MAP[$path]};
        }

        // Check if it's within a JSON column
        foreach (self::JSON_COLUMN_MAP as $prefix => $column) {
            if (str_starts_with($path, $prefix.'.')) {
                $subPath = substr($path, strlen($prefix) + 1);
                $jsonData = $this->{$column} ?? [];

                return $this->getNestedValue($jsonData, $subPath);
            }
            if ($path === $prefix) {
                return $this->{$column};
            }
        }

        return null;
    }

    /**
     * Set a value using dot notation path.
     * Routes to the correct column or JSON column.
     */
    public function setDataValueByPath(string $path, mixed $value): void
    {
        // Check if it maps to a direct column
        if (isset(self::FIELD_MAP[$path])) {
            $this->{self::FIELD_MAP[$path]} = $value;

            return;
        }

        // Check if it's within a JSON column
        foreach (self::JSON_COLUMN_MAP as $prefix => $column) {
            if (str_starts_with($path, $prefix.'.')) {
                $subPath = substr($path, strlen($prefix) + 1);
                $jsonData = $this->{$column} ?? [];
                $this->setNestedValue($jsonData, $subPath, $value);
                $this->{$column} = $jsonData;

                return;
            }
            if ($path === $prefix) {
                $this->{$column} = $value;

                return;
            }
        }
    }

    /**
     * Get a nested value from an array using dot notation.
     */
    private function getNestedValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $current = $data;

        foreach ($keys as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Set a nested value in an array using dot notation.
     */
    private function setNestedValue(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$data;
        $stringFields = ['phone', 'email', 'website', 'date', 'name', 'description'];

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                if ($value === '' || $value === null) {
                    unset($current[$key]);
                } elseif (is_numeric($value) && ! in_array($key, $stringFields)) {
                    $current[$key] = is_float($value + 0) ? (float) $value : (int) $value;
                } else {
                    $current[$key] = $value;
                }
            } else {
                if (! isset($current[$key]) || ! is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }
}
