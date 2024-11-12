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
        Schema::table('jembatan', function (Blueprint $table) {
            $table->string('status', 255)->nullable()->default('Menunggu')->after('longitude');
            $table->string('keterangan', 255)->nullable()->default('Menunggu diverifikasi')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jembatan', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('keterangan');
        });
    }
};
