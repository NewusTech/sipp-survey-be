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
            $table->decimal('telford', 8, 3)->after('lapen')->nullable();
            $table->integer('tahun');
            $table->decimal('baik', 8, 3)->after('tahun')->nullable();
            $table->decimal('sedang', 8, 3)->after('baik')->nullable();
            $table->decimal('rusak_ringan', 8, 3)->after('sedang')->nullable();
            $table->decimal('rusak_berat', 8, 3)->after('rusak_ringan')->nullable();

        });

        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->string('lebar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_perkerasan', function (Blueprint $table) {
            $table->dropColumn('telford');
            $table->dropColumn('tahun');
            $table->dropColumn('baik');
            $table->dropColumn('sedang');
            $table->dropColumn('rusak_ringan');
            $table->dropColumn('rusak_berat');
        });

        Schema::table('master_ruas_jalan', function (Blueprint $table) {
            $table->dropColumn('lebar');
        });
    }
};
