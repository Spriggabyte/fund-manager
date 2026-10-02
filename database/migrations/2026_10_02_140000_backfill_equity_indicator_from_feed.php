<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The EQUITY INDICATOR dot count is fed (`EQUITY_INDICATOR` in every
     * equity-holding fund's FACTSHEET export), but until 2 Oct 2026 no
     * importer read it. Funds rendered each template's hard-coded `?? n`
     * fallback, which was the Aug 2026 value. Fund 21 (818 A) on Sept data
     * therefore showed 6 dots where the feed says 5.
     *
     * FactsheetImporter now maps the key on every import. This backfills
     * funds already imported, using the export for the month each fund is on
     * (its fund_date). Every class of a fund code carries the same value.
     * Staging has no feed mirror, so the values are written out here rather
     * than read from storage. Funds on another month, and 879 (no
     * EQUITY_INDICATOR key in the feed), keep their stored value or template
     * fallback.
     */
    private const FEED = [
        '2026-08' => [
            '809' => 6, '810' => 7, '811' => 9, '816' => 7, '817' => 7,
            '818' => 6, '820' => 6, '821' => 9, '822' => 6, '823' => 9,
            '840' => 7, '874' => 6, '875' => 6, '877' => 9, '878' => 8,
        ],
        '2026-09' => [
            '809' => 5, '810' => 7, '811' => 9, '816' => 6, '817' => 7,
            '818' => 5, '820' => 7, '821' => 10, '822' => 5, '823' => 10,
            '840' => 7, '874' => 5, '875' => 5, '877' => 10, '878' => 9,
        ],
    ];

    public function up(): void
    {
        DB::table('funds')
            ->whereNotNull('equity_indicator_description')
            ->whereNotNull('fund_code')
            ->get(['id', 'fund_code', 'fund_date'])
            ->each(function ($fund) {
                try {
                    $month = Carbon::parse((string) $fund->fund_date)->format('Y-m');
                } catch (\Throwable) {
                    return;
                }

                $filled = self::FEED[$month][$fund->fund_code] ?? null;
                if ($filled !== null) {
                    DB::table('funds')->where('id', $fund->id)->update(['equity_indicator_filled' => $filled]);
                }
            });
    }

    public function down(): void
    {
        // Data backfill; the previous values were template fallbacks.
    }
};
