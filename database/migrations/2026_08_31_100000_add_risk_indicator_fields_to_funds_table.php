<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Prescient-branded feeder fact sheets (822) carry two sidebar rows
     * the Foord-branded sheets do not: RISK INDICATOR ("Moderate.") and the
     * RISK INDICATOR DEFINITION paragraph beneath it. `risk_of_loss` keeps
     * its own RISK OF LOSS meaning on the Foord sheets, so these get their
     * own columns rather than borrowing it.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->text('risk_indicator')->nullable()->after('risk_of_loss');
            $table->text('risk_indicator_definition')->nullable()->after('risk_indicator');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn(['risk_indicator', 'risk_indicator_definition']);
        });
    }
};
