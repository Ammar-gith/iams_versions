<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bill_classified_ads', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->integer('size')->nullable();
            $table->integer('printed_size')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('printed_rate', 10, 2)->nullable();
            $table->integer('no_of_insertion')->nullable();
            $table->integer('printed_no_of_insertion')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('printed_bill_cost', 10, 2)->nullable();
            $table->decimal('kpra_tax', 10, 2)->nullable();
            $table->decimal('printed_total_bill', 10, 2)->nullable();
            $table->string('press_cutting')->nullable();
            $table->string('scanned_bill')->nullable();
            $table->dateTime('publication_date')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_classified_ads');
    }
};
