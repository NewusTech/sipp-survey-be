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
            $table->string('no_ruas')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->integer('no_ruas')->change();
        });
    }
};
