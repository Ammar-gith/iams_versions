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
            $table->string('source_of_fund')->nullable()->after('forwarded_to_role_id');
            $table->string('adp_code')->nullable()->after('source_of_fund');
            $table->string('project_name')->nullable()->after('adp_code');
            $table->unsignedBigInteger('bill_submitted_to_role_id')->nullable(); // Tracks who last updated it

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisement', function (Blueprint $table) {
            $table->dropColumn('source_of_fund');
            $table->dropColumn('adp_code');
            $table->dropColumn('project_name');
            $table->dropColumn('bill_submitted_to_role_id');
        });
    }
};