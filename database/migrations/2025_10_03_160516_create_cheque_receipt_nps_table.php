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
        Schema::create('cheque_receipt_nps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challan_id')->nullable();
            $table->string('inf_number')->nullable();
            $table->unsignedBigInteger('newspaper_id')->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('it_department', 12, 2)->nullable();
            $table->decimal('it_inf', 12, 2)->nullable();
            $table->decimal('Kpra', 12, 2)->nullable();
            $table->decimal('sbp_charges', 12, 2)->nullable();
            $table->decimal('net_dues', 12, 2)->nullable();
            $table->decimal('received', 12, 2)->nullable();
            $table->decimal('balance', 12, 2)->nullable();
            $table->timestamps();


            // Foreign key constraints
            $table->foreign('challan_id')
                ->references('id')
                ->on('treasury_challans')
                ->onDelete('cascade'); // delete newspaper receipts if challan is deleted

            // Foreign key constraints
            $table->foreign('newspaper_id')
                ->references('id')
                ->on('newspapers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheque_receipt_nps');
    }
};
