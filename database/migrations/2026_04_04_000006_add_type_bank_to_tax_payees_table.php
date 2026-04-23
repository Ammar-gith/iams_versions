<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_payees', function (Blueprint $table) {
            $table->string('type')->nullable()->after('description'); // kpra | fbr
            $table->string('bank_name')->nullable()->after('type');
            $table->string('account_number')->nullable()->after('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('tax_payees', function (Blueprint $table) {
            $table->dropColumn(['type', 'bank_name', 'account_number']);
        });
    }
};
