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
        Schema::table('advertisements', function (Blueprint $table) {
            $table->json('suptd_NP_log')->nullable()->after('newspaper_id');
            $table->json('dd_NP_log')->nullable()->after('suptd_NP_log');
            $table->json('dg_NP_log')->nullable()->after('dd_NP_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['suptd_NP_log', 'dd_NP_log', 'dg_NP_log']);
        });
    }
};
