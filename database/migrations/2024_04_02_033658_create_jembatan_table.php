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
        Schema::create('jembatan', function (Blueprint $table) {
            $table->id();
            $table->string('no_ruas');
            $table->integer('kecamatan_id');

            $table->string('nama_ruas');
            $table->string('no_jembatan')->nullable();
            $table->string('asal')->nullable();
            $table->string('nama_jembatan')->nullable();
            $table->string('kmpost')->nullable();
            $table->string('panjang')->nullable();
            $table->string('lebar')->nullable();
            $table->string('jml_bentang')->nullable();
            $table->string('tipe_ba')->nullable();
            $table->string('kondisi_ba')->nullable();

            $table->string('tipe_bb')->nullable();
            $table->string('kondisi_bb')->nullable();

            $table->string('tipe_fondasi')->nullable();
            $table->string('kondisi_fondasi')->nullable();

            $table->string('bahan')->nullable();
            $table->string('kondisi_lantai')->nullable();

            $table->string('latitude')->nullable();
            $table->string('langitude')->nullable();

            $table->integer('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jembatan');
    }
};
