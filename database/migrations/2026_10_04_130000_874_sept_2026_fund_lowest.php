<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * WhatsApp amend 2 Oct 2026: the 874 International Trust sheet printed
     * "Fund lowest -100.0" in every column. The 874B export of 2 Oct 2026
     * sent FOORD_LOWEST_* = -100.0; since July those keys have tracked
     * another series (Y1–Y3 equal 880A's, whose own Sept returns are all
     * -100.0). The importer now rejects such rows (rollingReturnRow), but
     * the September import had already stored them.
     *
     * September 2026 lows: the Aug 2026 published values carry over. None of
     * their 12-month windows drops out of its period in September (874
     * price series: the lows sit at Feb 2008→Feb 2009, Jan 2023→Jan 2024 and
     * Dec 2023→Dec 2024; the windows that drop out return +2.7% to +11.9%),
     * and the 1-year cell is the fund's 1-year return.
     */
    private const SEPT_2026_LOWS = [
        'sinceInception' => -31.6,
        '10yrs' => -13.7,
        '5yrs' => -13.7,
        '3yrs' => -2.0,
    ];

    public function up(): void
    {
        DB::table('funds')
            ->where('fund_code', '874')
            ->where('fund_date', '30 September 2026')
            ->get(['id', 'performance_table'])
            ->each(function ($fund) {
                $table = json_decode($fund->performance_table ?? '[]', true) ?: [];
                $rows = $table['rows'] ?? [];
                $fundRow = collect($rows)->firstWhere('name', 'Fund');
                $changed = false;

                foreach ($rows as $i => $row) {
                    if (($row['name'] ?? null) !== 'Fund lowest') {
                        continue;
                    }
                    $lows = self::SEPT_2026_LOWS;
                    if (isset($fundRow['1yr'])) {
                        $lows['1yr'] = $fundRow['1yr'];
                    }
                    foreach ($lows as $key => $value) {
                        if (! isset($row[$key]) || $row[$key] <= -100) {
                            $rows[$i][$key] = $value;
                            $changed = true;
                        }
                    }
                }

                if ($changed) {
                    $table['rows'] = $rows;
                    DB::table('funds')->where('id', $fund->id)->update([
                        'performance_table' => json_encode($table, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Content correction; nothing to restore.
    }
};
