<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migrations
    public function up(): void
    {
        Schema::create('ad_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role'); // e.g. 'Deputy Director'
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    // Reverse the migrations
    public function down(): void
    {
        Schema::dropIfExists('ad_change_logs');
    }
};
