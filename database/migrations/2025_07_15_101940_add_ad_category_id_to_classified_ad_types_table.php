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
        Schema::table('classified_ad_types', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_category_id')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classified_ad_types', function (Blueprint $table) {
            $table->dropForeign(['ad_category_id']);
            $table->dropColumn('ad_category_id');
        });
    }
};
