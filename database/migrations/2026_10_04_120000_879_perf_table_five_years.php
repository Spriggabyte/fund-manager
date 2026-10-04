<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * WhatsApp amend 2 Oct 2026: the 879 Asia ex-Japan sheets (classes R
     * and R1) add a 5 YRS column and drop 6 MTHS / 3 MTHS, giving the 878
     * period set. The headers and column keys are seeded statics the
     * importer never writes, so only this migration moves staging and
     * production. The feed already supplies the 5-year values
     * (FOORD_*_5Y_TO_D, FOORD_HIGHEST/LOWEST_Y5).
     */
    private const HEADERS = ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '5<br>YRS', '3<br>YRS', '1<br>YR', 'YTD', 'THIS<br>MONTH'];

    private const COLUMN_KEYS = ['cashValue', 'sinceInception', '5yrs', '3yrs', '1yr', 'ytd', 'thisMonth'];

    public function up(): void
    {
        DB::table('funds')
            ->where('template', 'show-asia-ex-japan')
            ->get(['id', 'performance_table'])
            ->each(function ($fund) {
                $table = json_decode($fund->performance_table ?? '[]', true) ?: [];
                $table['headers'] = self::HEADERS;
                $table['columnKeys'] = self::COLUMN_KEYS;

                DB::table('funds')->where('id', $fund->id)->update([
                    'performance_table' => json_encode($table, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            });
    }

    public function down(): void
    {
        // Content correction; nothing to restore.
    }
};
