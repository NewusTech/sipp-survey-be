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
            $table->decimal('rigit', 8, 3)->nullable()->change();
            $table->decimal('hotmix', 8, 3)->nullable()->change();
            $table->decimal('lapen', 8, 3)->nullable()->change();

            $table->decimal('agregat', 8, 3)->nullable()->change();
            $table->decimal('onderlagh', 8, 3)->nullable()->change();
            $table->decimal('tanah', 8, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_perkerasan', function (Blueprint $table) {
            $table->integer('rigit')->change();
            $table->integer('hotmix')->change();
            $table->integer('lapen')->change();

            $table->integer('agregat')->change();
            $table->integer('onderlagh')->change();
            $table->integer('tanah')->change();
        });
    }
};
