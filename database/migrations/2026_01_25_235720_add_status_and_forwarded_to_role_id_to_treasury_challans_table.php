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
            $table->unsignedBigInteger('status_id')->nullable()->after('sbp_verification_date');

            $table->foreign('status_id')
                ->references('id')
                ->on('statuses');
            $table->unsignedBigInteger('forwarded_to_role_id')->nullable()->after('status_id');

            $table->foreignId('verified_by')->nullable()->after('forwarded_to_role_id')->constrained('users');
            $table->foreignId('approved_by')->nullable()->after('verified_by')->constrained('users');
            $table->timestamp('verified_at')->nullable()->after('approved_by');
            $table->timestamp('approved_at')->nullable()->after('verified_at');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treasury_challans', function (Blueprint $table) {
            $table->dropColumn(['status_id', 'forwarded_to_role_id', 'verified_by', 'approved_by', 'verified_at', 'approved_at', 'rejection_reason']);
        });
    }
};