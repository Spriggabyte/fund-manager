<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * funds.user_id used to mean "owner" and cascaded on delete. Funds are now
     * shared by the whole team and the column only records who created the
     * fund, so deleting a staff account must not take the funds with it.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('funds', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
