<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_pdf_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // The render variant in use today (the fund template). When the
            // jurisdiction/Shariah feature lands, add jurisdiction + variant here.
            $table->string('template')->nullable();
            // pending | processing | done | failed — plain string for sqlite parity.
            $table->string('status')->default('pending')->index();
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_pdf_exports');
    }
};
