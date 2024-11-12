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
        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->string('status', 255)->nullable()->default('Menunggu')->after('longitude');
            $table->string('alasan', 255)->nullable()->default('Menunggu diverifikasi')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('alasan');
        });
    }
};
