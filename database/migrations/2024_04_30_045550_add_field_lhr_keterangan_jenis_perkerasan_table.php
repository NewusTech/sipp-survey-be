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
        Schema::table('jenis_perkerasan', function (Blueprint $table) {
            $table->string('lhr')->nullable();
            $table->string('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_perkerasan', function (Blueprint $table) {
            $table->dropColumn('lhr')->nullable();
            $table->dropColumn('keterangan')->nullable();
        });
    }
};
