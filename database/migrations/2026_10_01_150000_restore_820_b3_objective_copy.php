<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Trello 465: the 820 Domestic Balanced B3 header paragraph should be
     * the August B3 reference's objective, not B2's. B3 was onboarded as
     * a copy of B2, and the B3 copy was corrected by hand in the dev
     * database only, so staging kept B2's paragraph. The paragraph is
     * static prose, not fed, so no import will fix it.
     */
    private const B3_COPY = 'The fund seeks to achieve steady growth of income and capital and the preservation of capital in real terms, managed to comply with the statutory limits set for retirement funds in South Africa (Regulation 28) with the additional constraint of no foreign assets. It is designed for pension funds and other long-term institutional investors who desire a balanced exposure to domestic-only assets within prudential investment guidelines.';

    public function up(): void
    {
        DB::table('funds')
            ->where('fund_code', '820')
            ->where(fn ($q) => $q->where('class_code', 'B3')->orWhere('name', 'like', '%CLASS B3'))
            ->update(['description' => self::B3_COPY]);
    }

    public function down(): void
    {
        // Content correction; nothing to restore.
    }
};
