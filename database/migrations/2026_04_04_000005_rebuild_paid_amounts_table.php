<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('paid_amounts');

        Schema::create('paid_amounts', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no', 30)->index();   // e.g. "Apr-2026-1"

            // Polymorphic-style payee: newspaper_id, agency_id, or tax_payee_id
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->string('payee_type', 20);          // newspaper | agency | kpra | fbr

            // Bank info (from media_bank_details, nullable for tax payees)
            $table->unsignedBigInteger('media_bank_detail_id')->nullable();

            // Payment details entered by user
            $table->string('cheque_no')->nullable();
            $table->decimal('amount', 15, 2);

            $table->timestamps();

            // Prevent saving the same payee twice for the same batch
            $table->unique(['batch_no', 'payee_type', 'payee_id'], 'paid_amounts_batch_payee_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_amounts');
    }
};
