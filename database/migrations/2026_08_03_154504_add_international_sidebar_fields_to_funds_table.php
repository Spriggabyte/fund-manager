<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sidebar fields specific to the international (Luxembourg) fact sheets:
     * depository, investment managers, share class details, the sidebar fee
     * summary and the Refinitiv Lipper award box.
     */
    public function up(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->string('depository')->nullable()->after('management_company');
            $table->string('investment_manager')->nullable()->after('depository');
            $table->string('sub_investment_manager')->nullable()->after('investment_manager');
            $table->string('type_of_shares')->nullable()->after('sub_investment_manager');
            $table->string('fees_summary')->nullable()->after('type_of_shares');
            $table->json('lipper_award')->nullable()->after('fees_summary');
            // Page-2 narrative sections (share pricing, more about the fund,
            // Lipper award paragraph, notes) used by the international layout.
            $table->json('page2_content')->nullable()->after('lipper_award');
        });
    }

    public function down(): void
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn([
                'depository',
                'investment_manager',
                'sub_investment_manager',
                'type_of_shares',
                'fees_summary',
                'lipper_award',
                'page2_content',
            ]);
        });
    }
};
