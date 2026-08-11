<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->json('sector_allocation')->nullable()->after('chart_data');
            $table->text('chart_description')->nullable()->after('sector_allocation');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn(['sector_allocation', 'chart_description']);
        });
    }
};
