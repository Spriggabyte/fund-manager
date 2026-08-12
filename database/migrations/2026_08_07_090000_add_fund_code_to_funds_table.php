<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maps a fund to its folder code on the monthly SFTP data feed
     * (remote layout: YYYY-MM/{fund_code}/*.xlsx). String rather than
     * integer in case codes gain a class suffix (e.g. 810A).
     *
     * Best-guess codes are seeded by fund name and can be corrected on
     * the fund edit page; Balanced Class B is left unmapped until its
     * remote code is confirmed.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->string('fund_code', 10)->nullable()->unique()->after('class');
        });

        $seed = [
            '810' => ['%BALANCED%', '%CLASS A%'],
            '811' => ['%EQUITY%', '%CLASS A%'],
            '817' => ['%FLEXIBLE%', '%CLASS A%'],
            '875' => ['%INTERNATIONAL%', '%CLASS R%'],
        ];

        foreach ($seed as $code => [$fund, $class]) {
            DB::table('funds')
                ->where('name', 'like', $fund)
                ->where('name', 'like', $class)
                ->whereNull('fund_code')
                ->limit(1)
                ->update(['fund_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('fund_code');
        });
    }
};
