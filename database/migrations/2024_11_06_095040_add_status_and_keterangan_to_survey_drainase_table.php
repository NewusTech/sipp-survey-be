<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('survey_drainase', function (Blueprint $table) {
            $table->string('status', 255)->nullable()->default('Menunggu')->after('kondisi');
            $table->string('keterangan', 255)->nullable()->default('Menunggu diverifikasi')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('survey_drainase', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('keterangan');
        });
    }
};
