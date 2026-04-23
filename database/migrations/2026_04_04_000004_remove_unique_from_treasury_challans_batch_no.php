<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treasury_challans', function (Blueprint $table) {
            // Multiple challans can share the same batch_no (one active batch at a time)
            $table->dropUnique(['batch_no']);
            $table->index('batch_no');
        });
    }

    public function down(): void
    {
        Schema::table('treasury_challans', function (Blueprint $table) {
            $table->dropIndex(['batch_no']);
            $table->unique('batch_no');
        });
    }
};
