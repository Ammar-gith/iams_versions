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
        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->string('payee_name')->nullable()->after('payee_id');
            $table->date('cheque_date')->nullable()->after('cheque_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->dropColumn('payee_name');
            $table->dropColumn('cheque_date');
        });
    }
};
