<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sidebar prose specific to the Foord International Trust (874) fact
     * sheet: the MASTER FUND paragraph (the trust invests exclusively in the
     * Master Fund) and the MASTER FUND RETURNS reconciliation note.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->text('master_fund')->nullable()->after('fees_summary');
            $table->text('master_fund_returns')->nullable()->after('master_fund');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn(['master_fund', 'master_fund_returns']);
        });
    }
};
