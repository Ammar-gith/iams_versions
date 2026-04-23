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
        Schema::create('media_bank_details', function (Blueprint $table) {
            $table->id();

            // Media info - only one will be filled per row
            $table->unsignedBigInteger('newspaper_id')->nullable();
            $table->unsignedBigInteger('agency_id')->nullable();

            // Bank details
            $table->string('media_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign keys
            $table->foreign('newspaper_id')->references('id')->on('newspapers')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('adv_agencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_bank_details');
    }
};
