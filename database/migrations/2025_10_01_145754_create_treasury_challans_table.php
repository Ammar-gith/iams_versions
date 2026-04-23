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
        Schema::create('treasury_challans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->json('inf_number')->nullable();
            $table->string('memo_number')->nullable();
            $table->dateTime('memo_date')->nullable();
            $table->bigInteger('cheque_number')->nullable();
            $table->dateTime('cheque_date')->nullable();
            $table->string('cheque_covering_letter_number')->nullable();
            $table->dateTime('cheque_covering_letter_date')->nullable();
            $table->decimal('newspapers_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->bigInteger('bank_account_number')->nullable();
            $table->dateTime('tr_challan_verification_date')->nullable();
            $table->string('challan_number')->nullable();
            $table->dateTime('sbp_verification_date')->nullable();


            // Foreign key constraints
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade'); // delete challans if department is deleted

            $table->foreign('office_id')
                ->references('id')
                ->on('offices')
                ->onDelete('cascade'); // delete challans if office is deleted

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_challans');
    }
};
