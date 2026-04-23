<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->json('placements')->nullable()->after('newspaper_id');
            $table->json('rates_with_placement')->nullable()->after('placements');
            $table->json('spaces')->nullable()->after('rates_with_placement');
            $table->json('total_spaces')->nullable()->after('spaces');
            $table->json('insertions')->nullable()->after('total_spaces');
            $table->json('total_cost_per_newspaper')->nullable()->after('insertions');
            $table->json('kpra_2_percent_on_85_percent_agency')->nullable()->after('total_cost_per_newspaper');
            $table->json('kpra_10_percent_on_15_percent_agency')->nullable()->after('kpra_2_percent_on_85_percent_agency');
            $table->json('total_amount_with_taxes')->nullable()->after('kpra_10_percent_on_15_percent_agency');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->dropColumn(['placements', 'rates_with_placement', 'spaces', 'total_spaces', 'insertions', 'total_cost_per_newspaper', 'kpra_2_percent_on_85_percent_agency', 'kpra_10_percent_on_15_percent_agency', 'total_amount_with_taxes']);
        });
    }
};
