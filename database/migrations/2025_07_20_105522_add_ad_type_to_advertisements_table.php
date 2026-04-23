<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('advertisements', function (Blueprint $table) {
        $table->string('ad_type')->after('status_id')->nullable();
    });
}

public function down()
{
    Schema::table('advertisements', function (Blueprint $table) {
        $table->dropColumn('ad_type');
    });
}
};
