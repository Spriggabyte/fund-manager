<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- Add columns to funds ---
        Schema::table('funds', function (Blueprint $table) {
            // Fund metadata
            $table->string('template')->nullable()->default('show');
            $table->string('fund_date')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();

            // Sidebar fields
            $table->string('category')->nullable();
            $table->string('domicile')->nullable();
            $table->string('minimums')->nullable();
            $table->text('benchmark')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('isin_number')->nullable();
            $table->string('sedol')->nullable();
            $table->text('risk_of_loss')->nullable();
            $table->string('time_horizon')->nullable();
            $table->string('base_currency')->nullable();
            $table->text('fund_managers')->nullable();
            $table->text('foreign_assets')->nullable();
            $table->string('inception_date')->nullable();
            $table->string('number_of_units')->nullable();
            $table->string('portfolio_size')->nullable();
            $table->text('equity_indicator_description')->nullable();
            $table->text('last_distributions')->nullable();
            $table->text('management_company')->nullable();
            $table->text('income_distributions')->nullable();
            $table->text('portfolio_orientation')->nullable();
            $table->text('income_characteristics')->nullable();
            $table->text('significant_restrictions')->nullable();

            // Footer
            $table->text('footer_info')->nullable();
            $table->string('footer_email')->nullable();
            $table->string('footer_phone')->nullable();
            $table->string('footer_website')->nullable();
            $table->text('footer_logo_url')->nullable();
            $table->string('footer_free_of_charge')->nullable();

            // Important info
            $table->string('important_info_title')->nullable();
            $table->string('important_info_published_date')->nullable();
            $table->json('important_info_paragraphs')->nullable();

            // Complex structured data (JSON columns)
            $table->json('asset_allocation')->nullable();
            $table->json('top_investments')->nullable();
            $table->json('performance_table')->nullable();
            $table->json('chart_data')->nullable();
            $table->json('fees')->nullable();
        });

        // --- Migrate existing data from JSON blob ---
        $funds = DB::table('funds')->whereNotNull('data')->get();
        foreach ($funds as $fund) {
            $data = json_decode($fund->data, true);
            if (! $data) {
                continue;
            }

            $update = [];

            // Fund metadata
            $update['template'] = $data['fund']['template'] ?? 'show';
            $update['fund_date'] = $data['fund']['date'] ?? null;
            $update['description'] = $data['fund']['description'] ?? null;
            $update['logo_url'] = $data['fund']['logoUrl'] ?? null;

            // Sidebar fields
            $sidebar = $data['sidebar'] ?? [];
            $update['category'] = $sidebar['category'] ?? null;
            $update['domicile'] = $sidebar['domicile'] ?? null;
            $update['minimums'] = $sidebar['minimums'] ?? null;
            $update['benchmark'] = $sidebar['benchmark'] ?? null;
            $update['unit_price'] = $sidebar['unitPrice'] ?? null;
            $update['isin_number'] = $sidebar['isinNumber'] ?? null;
            $update['sedol'] = $sidebar['sedol'] ?? null;
            $update['risk_of_loss'] = $sidebar['riskOfLoss'] ?? null;
            $update['time_horizon'] = $sidebar['timeHorizon'] ?? null;
            $update['base_currency'] = $sidebar['baseCurrency'] ?? null;
            $update['fund_managers'] = $sidebar['fundManagers'] ?? null;
            $update['foreign_assets'] = $sidebar['foreignAssets'] ?? null;
            $update['inception_date'] = $sidebar['inceptionDate'] ?? null;
            $update['number_of_units'] = $sidebar['numberOfUnits'] ?? null;
            $update['portfolio_size'] = $sidebar['portfolioSize'] ?? null;
            $update['equity_indicator_description'] = $sidebar['equityIndicator']['description'] ?? null;
            $update['last_distributions'] = $sidebar['lastDistributions'] ?? null;
            $update['management_company'] = $sidebar['managementCompany'] ?? null;
            $update['income_distributions'] = $sidebar['incomeDistributions'] ?? null;
            $update['portfolio_orientation'] = $sidebar['portfolioOrientation'] ?? null;
            $update['income_characteristics'] = $sidebar['incomeCharacteristics'] ?? null;
            $update['significant_restrictions'] = $sidebar['significantRestrictions'] ?? null;

            // Footer
            $footer = $data['footer'] ?? [];
            $update['footer_info'] = $footer['info'] ?? null;
            $update['footer_email'] = $footer['contact']['email'] ?? null;
            $update['footer_phone'] = $footer['contact']['phone'] ?? null;
            $update['footer_website'] = $footer['contact']['website'] ?? null;
            $update['footer_logo_url'] = $footer['logoUrl'] ?? null;
            $update['footer_free_of_charge'] = $footer['freeOfCharge'] ?? null;

            // Important info
            $importantInfo = $data['importantInfo'] ?? [];
            $update['important_info_title'] = $importantInfo['title'] ?? null;
            $update['important_info_published_date'] = $importantInfo['publishedDate'] ?? null;
            $update['important_info_paragraphs'] = isset($importantInfo['paragraphs']) ? json_encode($importantInfo['paragraphs']) : null;

            // Complex structured data
            $update['asset_allocation'] = isset($data['mainContent']['assetAllocation']) ? json_encode($data['mainContent']['assetAllocation']) : null;
            $update['top_investments'] = isset($data['mainContent']['topInvestments']) ? json_encode($data['mainContent']['topInvestments']) : null;
            $update['performance_table'] = isset($data['mainContent']['performanceTable']) ? json_encode($data['mainContent']['performanceTable']) : null;
            $update['chart_data'] = isset($data['mainContent']['charts']) ? json_encode($data['mainContent']['charts']) : null;
            $update['fees'] = isset($data['fees']) ? json_encode($data['fees']) : null;

            DB::table('funds')->where('id', $fund->id)->update($update);
        }

        // Drop the old data column
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }

    public function down(): void
    {
        // Re-add data column and migrate back
        Schema::table('funds', function (Blueprint $table) {
            $table->json('data')->nullable();
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn([
                'template', 'fund_date', 'description', 'logo_url',
                'category', 'domicile', 'minimums', 'benchmark', 'unit_price',
                'isin_number', 'sedol', 'risk_of_loss', 'time_horizon', 'base_currency',
                'fund_managers', 'foreign_assets', 'inception_date', 'number_of_units',
                'portfolio_size', 'equity_indicator_description', 'last_distributions',
                'management_company', 'income_distributions', 'portfolio_orientation',
                'income_characteristics', 'significant_restrictions',
                'footer_info', 'footer_email', 'footer_phone', 'footer_website',
                'footer_logo_url', 'footer_free_of_charge',
                'important_info_title', 'important_info_published_date', 'important_info_paragraphs',
                'asset_allocation', 'top_investments', 'performance_table', 'chart_data', 'fees',
            ]);
        });
    }
};
