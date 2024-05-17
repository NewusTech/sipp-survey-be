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
        Schema::table('kondisi_perkerasan', function (Blueprint $table) {
            $table->decimal('baik', 8, 3)->nullable()->change();
            $table->decimal('sedang', 8, 3)->nullable()->change();
            $table->decimal('rusak_ringan', 8, 3)->nullable()->change();
            $table->decimal('rusak_berat', 8, 3)->nullable()->change();
        });

        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->decimal('panjang_ruas', 8, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kondisi_perkerasan', function (Blueprint $table) {
            $table->integer('baik')->change();
            $table->integer('sedang')->change();
            $table->integer('rusak_ringan')->change();
            $table->integer('rusak_berat')->change();
        });

        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->integer('panjang_ruas')->change();
        });
    }
};
