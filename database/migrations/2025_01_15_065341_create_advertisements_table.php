<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Run the migration
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('inf_number', 20)->nullable()->unique();
            $table->string('memo_number')->nullable();
            $table->datetime('memo_date')->nullable();
            $table->datetime('publish_on_or_before')->nullable();
            $table->integer('urdu_size')->nullable();
            $table->integer('english_size')->nullable();
            $table->integer('urdu_lines')->nullable();
            $table->integer('english_lines')->nullable();
            $table->string('remarks')->nullable();

            // Foreign keys with cascading behavior
            $table->json('ad_rejection_reasons_id')->nullable();

            // Foreign keys with cascading behavior
            $table->unsignedBigInteger('inf_series_id')->nullable();
            $table->foreign('inf_series_id')
                ->references('id')
                ->on('inf_series')
                ->onDelete('cascade');

            // Foreign keys with cascading behavior
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');

            // Foreign keys with cascading behavior
            $table->unsignedBigInteger('office_id')->nullable();
            $table->foreign('office_id')
                ->references('id')
                ->on('offices')
                ->onDelete('cascade');

            // Foreign keys with cascading behavior
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Foreign keys with cascading behavior
            $table->unsignedBigInteger('ad_category_id')->nullable();
            $table->foreign('ad_category_id')
                ->references('id')
                ->on('ad_categories');

            $table->unsignedBigInteger('ad_worth_id')->nullable();
            $table->foreign('ad_worth_id')
                ->references('id')
                ->on('ad_worth_parameters');

            $table->unsignedBigInteger('news_pos_rate_id')->nullable();
            $table->foreign('news_pos_rate_id')
                ->references('id')
                ->on('news_pos_rates');

            $table->json('newspaper_id')->nullable();


            $table->unsignedBigInteger('adv_agency_id')->nullable();
            $table->foreign('adv_agency_id')
                ->references('id')
                ->on('adv_agencies');


            $table->unsignedBigInteger('status_id')->nullable();
            $table->foreign('status_id')
                ->references('id')
                ->on('statuses');

            // publication column
            $table->string('publication')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('forwarded_by_role_id')->nullable(); // Tracks who last updated it
            $table->unsignedBigInteger('forwarded_to_role_id')->nullable(); // Tracks who last updated it

            $table->timestamps();
        });
    }

    // Reverse the migration
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
