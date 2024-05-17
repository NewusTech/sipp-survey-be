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
        Schema::table('survey_drainase', function (Blueprint $table) {
            $table->string('latitude')->after('kondisi')->nullable();
            $table->string('longitude')->after('latitude')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_drainase', function (Blueprint $table) {
            $table->dropColumn('latitude')->nullable();
            $table->dropColumn('longitude')->nullable();
        });
    }
};
