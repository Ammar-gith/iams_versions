<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations
    public function up(): void
    {
        Schema::create('inf_series', function (Blueprint $table) {
            $table->id();
            $table->string('series')->unique(); // INF Series (e.g., XXXXX-25)
            $table->integer('start_number')->default(00001); // Starting number
            $table->integer('issued_numbers')->default(0); // Total numbers issued
            $table->timestamps();
        });
    }

    // Reverse the migrations
    public function down(): void
    {
        Schema::dropIfExists('inf_series');
    }
};

