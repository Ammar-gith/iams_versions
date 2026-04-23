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
        Schema::table('newspapers', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->nullable()->after('title');
            $table->unsignedBigInteger('district_id')->nullable()->after('language_id');
            $table->unsignedBigInteger('province_id')->nullable()->after('district_id');
            $table->tinyInteger('is_combined')->default(0)->after('province_id');
            $table->string('daily_circulation')->nullable()->after('is_combined');
            $table->decimal('rate', 15, 2)->nullable()->after('daily_circulation');
            $table->date('rate_efc_date')->nullable()->after('rate');
            $table->unsignedBigInteger('periodicity_id')->nullable()->after('rate_efc_date');
            $table->unsignedBigInteger('category_id')->nullable()->after('periodicity_id');
            $table->date('registration_date')->nullable()->after('category_id');
            $table->integer('phone_no')->nullable()->after('registration_date');
            $table->string('email')->nullabl()->after('phone_no');
            $table->string('fax')->nullable()->after('email');
            $table->string('website')->nullable()->after('fax');
            $table->string('fp_name')->nullable()->after('website');
            $table->integer('cell_no')->nullable()->after('fp_name');
            $table->tinyInteger('status')->default(1)->after('cell_no');
            $table->decimal('opening_balance', 15, 2)->nullable()->after('status');
            $table->string('register_with_kapra')->nullable()->after('opening_balance');


            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
            $table->foreign('periodicity_id')->references('id')->on('newspaper_periodicities')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('newspaper_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newspapers', function (Blueprint $table) {
            $table->dropColumn([
                'language_id',
                'district_id',
                'province_id',
                'is_combined',
                'daily_circulation',
                'rate',
                'rate_efc_date',
                'periodicity_id',
                'category_id',
                'registration_date',
                'phone_no',
                'email',
                'fax',
                'website',
                'fp_name',
                'cell_no',
                'status',
                'opening_balance',
                'register_with_kapra'
            ]);
        });
    }
};
