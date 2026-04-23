<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations
    public function up(): void
    {
        Schema::create('classified_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classified_ad_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    // Reverse the migrations
    public function down(): void
    {
        Schema::dropIfExists('classified_ads');
    }
};
