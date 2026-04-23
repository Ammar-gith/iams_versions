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
        Schema::create('pla_acounts', function (Blueprint $table) {
            $table->id();
            $table->json('inf_number')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->bigInteger('cheque_no')->nullable();
            $table->dateTime('cheque_date')->nullable();
            $table->string('challan_no')->nullable();
            $table->json('newspaper_id')->nullable();
            $table->json('newspaper_amount')->nullable();
            $table->decimal('total_cheque_amount', 12, 2)->nullable();


            // Foreign key constraints
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade'); // delete challans if department is deleted

            $table->foreign('office_id')
                ->references('id')
                ->on('offices')
                ->onDelete('cascade'); // delete challans if office is deleted

            // Foreign key constraints
            // $table->foreign('newspaper_id')
            //     ->references('id')
            //     ->on('newspapers')
            //     ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pla_acounts');
    }
};
