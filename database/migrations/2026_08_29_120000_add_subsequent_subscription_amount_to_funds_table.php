<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Global Equity Fund (Luxembourg) sidebar splits the subscription
     * minimums into MINIMUM SUBSCRIPTION AMOUNT (the existing `minimums`
     * column) and SUBSEQUENT SUBSCRIPTION AMOUNT (876 reference).
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->text('subsequent_subscription_amount')->nullable()->after('minimums');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('subsequent_subscription_amount');
        });
    }
};
