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
            $table->string('ledger_batch_no', 30)->nullable()->after('batch_no');
        });

        DB::table('paid_amounts')->update([
            'ledger_batch_no' => DB::raw('batch_no'),
        ]);

        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->dropUnique('paid_amounts_batch_payee_unique');
        });

        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->unique(['ledger_batch_no', 'payee_type', 'payee_id'], 'paid_amounts_ledger_payee_unique');
        });
    }

    public function down(): void
    {
        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->dropUnique('paid_amounts_ledger_payee_unique');
        });

        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->unique(['batch_no', 'payee_type', 'payee_id'], 'paid_amounts_batch_payee_unique');
        });

        Schema::table('paid_amounts', function (Blueprint $table) {
            $table->dropColumn('ledger_batch_no');
        });
    }
};
