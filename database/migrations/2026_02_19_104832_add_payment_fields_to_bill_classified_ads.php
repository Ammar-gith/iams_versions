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
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
            $table->dropColumn('payment_status');
        });
    }
};
