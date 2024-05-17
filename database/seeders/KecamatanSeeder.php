<?php

/*
 * This file is part of the IndoRegion package.
 *
 * (c) Azis Hapidin <azishapidin.com | azishapidin@gmail.com>
 *
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @deprecated
     * 
     * @return void
     */
    public function run()
    {
        $data = [
            'Batu Putih',
            'Gunung Agung',
            'Gunung Terang',
            'Lambu Kibang',
            'Pagar Dewa',
            'Tulang Bawang Tengah',
            'Tulang Bawang Udik',
            'Tumijajar',
            'Way Kenanga'
        ];

        foreach ($data as $key => $item) {
            DB::table('kecamatan')->insert([
                'name' => $item
            ]);
        }

    }
}
