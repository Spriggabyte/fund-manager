<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Trello 314: the sidebar FOREIGN ASSETS line reads "...Foord global
     * funds in Luxembourg." on the August references (810 Balanced, 817
     * Flexible, 818 Conservative). The old "...in Luxembourg and Singapore."
     * was corrected by hand in the dev database during the September QC
     * rounds, so staging and production kept it. The line is static prose,
     * not fed, so no import will fix it.
     */
    private const OLD = 'Foord global funds in Luxembourg and Singapore.';

    private const NEW = 'Foord global funds in Luxembourg.';

    public function up(): void
    {
        DB::table('funds')
            ->where('foreign_assets', 'like', '%'.self::OLD.'%')
            ->get(['id', 'foreign_assets'])
            ->each(fn ($fund) => DB::table('funds')->where('id', $fund->id)->update([
                'foreign_assets' => str_replace(self::OLD, self::NEW, $fund->foreign_assets),
            ]));
    }

    public function down(): void
    {
        // Content correction; nothing to restore.
    }
};
