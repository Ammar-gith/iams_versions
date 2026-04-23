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
        Schema::table('treasury_challans', function (Blueprint $table) {
            $table->string('tr_challan_image')->nullable()->after('bank_account_number');
            $table->foreignId('created_by')->nullable()->after('rejection_reason')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treasury_challans', function (Blueprint $table) {
            $table->dropColumn('tr_challan_image');
            $table->dropColumn('created_by');
        });
    }
};