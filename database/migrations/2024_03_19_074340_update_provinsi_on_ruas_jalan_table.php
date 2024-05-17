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
        Schema::table('master_ruas_jalan', function(Blueprint $table) {
            $table->renameColumn('provinsi_id', 'provinsi');
            $table->renameColumn('kabupaten_id', 'kabupaten');
            $table->renameColumn('kecamatan_id', 'kecamatan');
            $table->renameColumn('desa_id', 'desa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ruas_jalan', function(Blueprint $table) {
            $table->renameColumn('provinsi', 'provinsi_id');
            $table->renameColumn('kabupaten', 'kabupaten_id');
            $table->renameColumn('kecamatan', 'kecamatan_id');
            $table->renameColumn('desa', 'desa_id');
        });
    }
};
