<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newspaper_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newspaper_id')->constrained()->cascadeOnDelete();
            $table->string('partner_name');
            $table->decimal('share_percentage', 5, 2);
            $table->foreignId('media_bank_detail_id')->constrained('media_bank_details')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['newspaper_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newspaper_partners');
    }
};
