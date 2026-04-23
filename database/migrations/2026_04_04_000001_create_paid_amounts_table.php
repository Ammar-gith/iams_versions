<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paid_amounts', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->string('payee_name');
            $table->string('payee_type'); // NP, AGENCY, KPRA, FBR
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->string('status')->default('paid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_amounts');
    }
};
