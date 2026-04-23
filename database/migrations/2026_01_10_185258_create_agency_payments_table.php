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
        Schema::create('agency_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id');
            $table->decimal('grand_amount', 15, 2);
            $table->decimal('gross_amount_15_percent', 15, 2)->default(0);
            $table->decimal('it_inf', 15, 2)->default(0);
            $table->decimal('it_department', 15, 2)->default(0);
            $table->decimal('kpra_inf', 15, 2)->default(0);
            $table->decimal('kpra_department', 15, 2)->default(0);
            $table->decimal('sbp_charges', 15, 2)->default(0);
            $table->decimal('adjustment', 15, 2)->default(0);
            $table->decimal('net_dues', 15, 2)->default(0);
            $table->decimal('received', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('remarks', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_payments');
    }
};
