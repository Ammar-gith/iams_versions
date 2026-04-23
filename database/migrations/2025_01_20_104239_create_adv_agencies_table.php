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
        Schema::create('adv_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('registration_date')->nullable();
            $table->boolean('registered_with_kpra')->nullable();
            $table->string('website')->nullable();
            $table->string('profile_pba')->nullable();
            $table->foreignId('status_id')->nullable();
            $table->integer('phone_local')->nullable();
            $table->string('email_local')->nullable();
            $table->string('fax_local')->nullable();
            $table->string('mailing_address_local')->nullable();
            $table->string('person_name_local')->nullable();
            $table->integer('person_cell_local')->nullable();
            $table->integer('phone_hq')->nullable();
            $table->string('email_hq')->nullable();
            $table->string('fax_hq')->nullable();
            $table->string('mailing_address_hq')->nullable();
            $table->string('person_name_hq')->nullable();
            $table->integer('person_cell_hq')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adv_agencies');
    }
};
