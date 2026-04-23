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
        Schema::table('agency_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('media_bank_detail_id')->nullable()->after('agency_id');
            $table->foreign('media_bank_detail_id')->references('id')->on('media_bank_details')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency_payments', function (Blueprint $table) {
            $table->dropForeign(['media_bank_detail_id']);
            $table->dropColumn('media_bank_detail_id');
        });
    }
};