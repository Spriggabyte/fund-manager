<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Foord Global Equity Australian Feeder Fund (880) sidebar carries
     * five rows no other fact sheet has: the Australian responsible entity
     * and custodian, the local distribution partner, the FUND FEATURES
     * bullet list, and the APIR / ARSN registration codes beneath the ISIN.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->string('responsible_entity')->nullable()->after('management_company');
            $table->string('custodian')->nullable()->after('responsible_entity');
            $table->string('distribution_partner')->nullable()->after('custodian');
            $table->text('fund_features')->nullable()->after('distribution_partner');
            $table->string('apir_arsn')->nullable()->after('isin_number');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn([
                'responsible_entity',
                'custodian',
                'distribution_partner',
                'fund_features',
                'apir_arsn',
            ]);
        });
    }
};
