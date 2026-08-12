<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Accounts are provisioned by an admin rather than self-registered, which
     * needs three things the stock Breeze users table has no room for: who may
     * manage other accounts, whether an account is switched off, and whether
     * the admin-chosen password still has to be replaced by the user.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->timestamp('disabled_at')->nullable()->after('is_admin');
            $table->boolean('must_change_password')->default(false)->after('disabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'disabled_at', 'must_change_password']);
        });
    }
};
