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
        Schema::create('survey_drainase', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ruas_drainase_id');
            $table->integer('panjang_drainase')->nullable();
            $table->string('letak_drainase')->nullable();
            $table->string('lebar_atas')->nullable();
            $table->string('lebar_bawah')->nullable();
            $table->string('tinggi')->nullable();
            $table->string('kondisi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_drainase');
    }
};
