<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Foord-Hassen Shariah Global Equity Fund (878) sidebar carries a
     * SHARIAH SUPERVISORY BOARD row between MANAGEMENT COMPANY and
     * DEPOSITARY that no other fact sheet has.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->string('shariah_supervisory_board')->nullable()->after('management_company');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('shariah_supervisory_board');
        });
    }
};
