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
        Schema::create('pla_account_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pla_acount_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('inf_number');

            $table->foreignId('newspaper_id')->nullable();
            $table->decimal('newspaper_amount', 15, 2)->default()->nullable();

            $table->foreignId('adv_agency_id')->nullable();
            $table->decimal('agency_commission_amount', 15, 2)->default()->nullable();
            $table->decimal('net_payable', 15, 2)->default()->nullable();


            $table->json('inf_details')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pla_account_items');
    }
};
