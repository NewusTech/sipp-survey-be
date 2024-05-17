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
        Schema::create('master_koridor', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('master_ruas_jalan', function (Blueprint $table) {
            $table->id();
            $table->integer('no_ruas')->nullable();
            $table->string('nama');
            $table->unsignedBigInteger('koridor_id');
            $table->integer('panjang_ruas');
            $table->string('akses')->nullable();
            $table->string('provinsi_id')->nullable();
            $table->string('kabupaten_id')->nullable();
            $table->string('kecamatan_id')->nullable();
            $table->string('desa_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('koridor_id')->references('id')->on('master_koridor');
        });

        Schema::create('jenis_perkerasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruas_jalan_id');
            $table->integer('rigit')->nullable();
            $table->integer('hotmix')->nullable();
            $table->integer('lapen')->nullable();
            $table->integer('agregat')->nullable();
            $table->integer('onderlagh')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ruas_jalan_id')->references('id')->on('master_ruas_jalan');
        });

        Schema::create('kondisi_perkerasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruas_jalan_id');
            $table->integer('baik')->nullable();
            $table->integer('sedang')->nullable();
            $table->integer('rusak_ringan')->nullable();
            $table->integer('rusak_berat')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ruas_jalan_id')->references('id')->on('master_ruas_jalan');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->dropForeign(['koridor_id']);
        });
        Schema::table('jenis_perkerasan', function (Blueprint $table) {
            $table->dropForeign(['ruas_jalan_id']);
        });
        Schema::table('kondisi_perkerasan', function (Blueprint $table) {
            $table->dropForeign(['ruas_jalan_id']);
        });
        Schema::dropIfExists('master_koridor');
        Schema::dropIfExists('master_ruas_jalan');
        Schema::dropIfExists('jenis_perkerasan');
        Schema::dropIfExists('kondisi_perkerasan');
        Schema::dropIfExists('users');
    }
};
