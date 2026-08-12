<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The unit-trust share class this fund record represents (A, B, B2, B3,
     * R, R1), matching the class token in the data-feed filenames:
     * {fund_code}{class_code}_FACTSHEET.xlsx.
     *
     * Distinct from the existing `class` column, which is an *asset* class
     * (Equity/Bond/Mixed/...) shown as a badge on the fund index.
     *
     * A fund code covers several classes, so uniqueness moves from fund_code
     * alone to the (fund_code, class_code) pair — three records share code 810.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->string('class_code', 6)->nullable()->after('fund_code');
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->dropUnique('funds_fund_code_unique');
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->unique(['fund_code', 'class_code']);
        });

        // Existing funds all point at the Class A export except international,
        // which the feed publishes as 875R.
        foreach (['A' => ['810', '811', '817'], 'R' => ['875']] as $classCode => $codes) {
            DB::table('funds')
                ->whereIn('fund_code', $codes)
                ->whereNull('class_code')
                ->update(['class_code' => $classCode]);
        }

        // Balanced "Class B" predates the feed mapping. The 810 folder ships
        // B2 and B3 only (no plain 810B), so this record becomes Class B2.
        // Renamed in PHP rather than SQL so the migration runs on both MySQL
        // and the SQLite database the test suite uses.
        $balancedB = DB::table('funds')
            ->whereNull('fund_code')
            ->where('name', 'like', '%BALANCED%')
            ->where('name', 'like', '%CLASS B')
            ->first();

        if ($balancedB) {
            DB::table('funds')->where('id', $balancedB->id)->update([
                'name' => $balancedB->name.'2',
                'fund_code' => '810',
                'class_code' => 'B2',
            ]);
        }
    }

    public function down(): void
    {
        $balancedB2 = DB::table('funds')
            ->where('fund_code', '810')
            ->where('class_code', 'B2')
            ->where('name', 'like', '%BALANCED%CLASS B2')
            ->first();

        if ($balancedB2) {
            DB::table('funds')->where('id', $balancedB2->id)->update([
                'name' => mb_substr($balancedB2->name, 0, -1),
                'fund_code' => null,
            ]);
        }

        Schema::table('funds', function (Blueprint $table) {
            $table->dropUnique('funds_fund_code_class_code_unique');
            $table->dropColumn('class_code');
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->unique('fund_code');
        });
    }
};
