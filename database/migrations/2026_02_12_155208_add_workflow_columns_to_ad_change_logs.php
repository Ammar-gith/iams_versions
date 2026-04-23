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
        Schema::table('ad_change_logs', function (Blueprint $table) {
            $table->string('action')->nullable()->after('field'); // e.g. 'forward', 'approve', 'send_back'
            $table->string('from_status')->nullable()->after('action');
            $table->string('to_status')->nullable()->after('from_status');
            $table->foreignId('assigned_to_id')->nullable()->after('to_status')->constrained('users');
            $table->json('metadata')->nullable()->after('assigned_to_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_change_logs', function (Blueprint $table) {
            $table->dropColumn(['action', 'from_status', 'to_status', 'assigned_to_id', 'metadata']);
        });
    }
};
