<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The equity-indicator dot count is static per fund (set from the
     * signed-off design, not the Excel feed). Both default to 10 so
     * existing funds render exactly as before (10 filled of 10 total).
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->unsignedTinyInteger('equity_indicator_filled')->nullable()->after('equity_indicator_description');
            $table->unsignedTinyInteger('equity_indicator_total')->nullable()->after('equity_indicator_filled');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn(['equity_indicator_filled', 'equity_indicator_total']);
        });
    }
};
