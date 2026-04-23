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
            $table->decimal('total_newspapers_tax', 10, 2)->nullable()->after('kpra_2_percent_on_85_percent_newspaper');
                $table->decimal('total_agency_tax', 10, 2)->nullable()->after('kpra_10_percent_on_15_percent_agency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->dropColumn(['total_newspapers_tax', 'total_agency_tax']);
        });
    }
};
