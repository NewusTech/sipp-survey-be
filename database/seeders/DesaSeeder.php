<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Batu Putih
        // Gunung Agung
        // Gunung Terang
        // Lambu Kibang
        // Pagar Dewa
        // Tulang Bawang Tengah
        // Tulang Bawang Udik
        // Tumijajar
        // Way Kenanga
        $desa1 = [
            'MULYO SARI',
            'MARGA SARI',
            'TOTO WONODADI',
            'SIDO MAKMUR',
            'PANCA MARGA',
            'TOTO KATON',
            'TOTO MAKMUR',
            'MARGO DADI',
            'MARGA MULYO',
            'SAKTI JAYA'
        ];
        $select1 = DB::table('kecamatan')->where('name', 'Batu Putih')->first();
        foreach ($desa1 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select1->id,
                'nama' => $item
            ]);
        }

        $desa2 = [
            'MARGA JAYA',
            'MEKAR JAYA',
            'SUKA JAYA',
            'MULYA JAYA',
            'WONO REJO',
            'SUMBER JAYA',
            'BANGUN JAYA',
            'TUNAS JAYA',
            'JAYA MURNI',
            'TRI TUNGGAL JAYA',
            'MULYA SARI',
            'DWIKORA JAYA',
            'SUMBER REJEKI',
        ];
        $selectGunungAgung = DB::table('kecamatan')->where('name', 'Gunung Agung')->first();
        foreach ($desa2 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $selectGunungAgung->id,
                'nama' => $item
            ]);
        }

        $desa3 = [
            'GUNUNG TERANG',
            'GUNUNG AGUNG',
            'TOTO MULYO',
            'SETIA BUMI',
            'KAGUNGAN JAYA',
            'TERANG MULYA',
            'TERANG BUMI AGUNG',
            'SETIA AGUNG',
            'MULYO JADI',
            'TERANG MAKMUR'
        ];
        $select3 = DB::table('kecamatan')->where('name', 'Gunung Terang')->first();
        foreach ($desa3 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select3->id,
                'nama' => $item
            ]);
        }

        $desa4 = [
            'LESUNG BAKTI JAYA',
            'MEKAR SARI JAYA',
            'PAGAR JAYA',
            'SUMBER REJO',
            'GUNUNG SARI',
            'KIBANG BUDI JAYA',
            'KIBANG YEKTI JAYA',
            'KIBANG TRI JAYA',
            'GILANG TUNGGAL MAKARTA',
            'KIBANG MULYA JAYA'
        ];
        $select4 = DB::table('kecamatan')->where('name', 'Lambu Kibang')->first();
        foreach ($desa4 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select4->id,
                'nama' => $item
            ]);
        }

        $desa5 = [
            'PAGAR DEWA',
            'BUJUNG DEWA',
            'BUJUNG SARI MARGA',
            'PAGAR DEWA SUKA MULYA',
            'CAHYOU RANDU',
            'MARGA JAYA INDAH'
        ];
        $select5 = DB::table('kecamatan')->where('name', 'Pagar Dewa')->first();
        foreach ($desa5 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select5->id,
                'nama' => $item
            ]);
        }

        $desa6 = [
            'MULYA ASRI',
            'CANDRA KENCANA',
            'MULYA KENCANA',
            'PULUNG KENCANA',
            'TIRTA KENCANA',
            'PANARAGAN JAYA',
            'PENUMANGAN',
            'PENUMANGAN BARU',
            'PANARAGAN',
            'BANDAR DEWA',
            'MENGGALA MAS',
            'TUNAS ASRI',
            'WONOKERTO',
            'PANARAGAN JAYA UTAMA',
            'PANARAGAN JAYA INDAH',
            'MULYA JAYA',
            'TIRTA MAKMUR',
            'CANDRA MUKTI',
            'CANDRA JAYA'
        ];
        $select6 = DB::table('kecamatan')->where('name', 'Tulang Bawang Tengah')->first();
        foreach ($desa6 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select6->id,
                'nama' => $item
            ]);
        }

        $desa7 = [
            'MARGA KENCANA',
            'KAGUNGAN RATU',
            'KARTA RAHARJA',
            'WAY SIDO',
            'KARTA SARI',
            'KARTA',
            'GUNUNG KATUN MALAY',
            'GUNUNG KATUN TANJUNGAN',
            'GEDUNG RATU'
        ];
        $select7 = DB::table('kecamatan')->where('name', 'Tulang Bawang Udik')->first();
        foreach ($desa7 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select7->id,
                'nama' => $item
            ]);
        }

        $desa8 = [
            'GUNUNG MENANTI',
            'MARGO DADI',
            'MURNI JAYA',
            'MARGO MULYO',
            'DAYA ASRI',
            'DAYA MURNI',
            'DAYA SAKTI',
            'MAKARTI',
            'SUMBER REJO',
            'GUNUNG TIMBUL'
        ];
        $select8 = DB::table('kecamatan')->where('name', 'Tumijajar')->first();
        foreach ($desa8 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select8->id,
                'nama' => $item
            ]);
        }

        $desa9 = [
            'AGUNG JAYA',
            'MERCU BUANA',
            'BALAM JAYA',
            'INDRALOKA II',
            'PAGAR BUANA',
            'INDRALOKA I',
            'BALAM ASRI',
            'INDRALOKA MUKTI',
            'INDRALOKA JAYA'
        ];
        $select9 = DB::table('kecamatan')->where('name', 'Way Kenanga')->first();
        foreach ($desa9 as $key => $item) {
            DB::table('master_desa')->insert([
                'kecamatan_id' => $select9->id,
                'nama' => $item
            ]);
        }
    }
}
