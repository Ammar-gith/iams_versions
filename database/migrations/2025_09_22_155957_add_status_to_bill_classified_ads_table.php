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
            $table->string('status')->nullable()->after('newspaper_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};