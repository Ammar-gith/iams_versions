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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('ddo_name');
            $table->string('ddo_code');
            $table->foreignId('department_id')->constrained();
            $table->foreignId('district_id')->constrained();
            $table->foreignId('office_category_id')->constrained();
            $table->foreignId('status');
            $table->decimal('opening_dues', 10, 2)->nullable();
            $table->date('deactivation_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
