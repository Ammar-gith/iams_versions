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
            $table->json('newspaper_share_amounts')->nullable()->after('total_cost_per_newspaper');
            $table->json('agency_share_amounts')->nullable()->after('kpra_2_percent_on_85_percent_newspaper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_classified_ads', function (Blueprint $table) {
            $table->dropColumn('newspaper_share_amounts');
            $table->dropColumn('agency_shares_amounts');
        });
    }
};
