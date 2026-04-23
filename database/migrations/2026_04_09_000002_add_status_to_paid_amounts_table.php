<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->string('status', 20)->default('paid')->after('amount'); // paid | reversed
        });

        // Backfill: old "removed/deferred" rows were stored as cheque_no = null and amount = 0
        DB::table('paid_amounts')
            ->whereNull('cheque_no')
            ->where('amount', 0)
            ->update(['status' => 'reversed']);
    }

    public function down(): void
    {
        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

