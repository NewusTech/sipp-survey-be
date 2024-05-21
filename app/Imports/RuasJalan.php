<?php

namespace App\Imports;

use App\Models\JenisPerkerasan;
use App\Models\Kecamatan;
use App\Models\RuasJalan as ModelsRuasJalan;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB as FacadesDB;

class RuasJalan implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        
        $data = $collection;
        if ($data) {
            unset(
                $data[0], 
                $data[1],
                $data[2],
                $data[3],
                $data[4],
                $data[5]
            );

            $messages = [];
            FacadesDB::beginTransaction();
            try {
                foreach ($data as $key => $item) {
                    // check no_ruas
                    // $existRuas = ModelsRuasJalan::where('no_ruas', $item[0])->exists();
                    // if ($existRuas) {
                    //     $messages[] = 'Data no_ruas sudah ada, no_ruas tidak boleh duplicate';
                    //     return;
                    // }
    
                    // check nama ruas
                    // $existNameRuas = ModelsRuasJalan::where('nama', $item[1])->exists();
                    // if ($existNameRuas) {
                    //     $messages[] = 'Data nama ruas sudah ada, nama ruas tidak boleh duplicate';
                    //     return;
                    // }
    
                    // if ($existRuas && $existNameRuas) {
                    //     $messages[] = 'gagal import, data tidak boleh sama';
                    //     return;
                    // }
                    $originName = trim($item[2]); // Remove leading/trailing whitespace
                    $lowercaseWord = strtolower($originName); // Convert to lowercase
                    $selectKecamatan = Kecamatan::whereRaw('LOWER(TRIM(name)) = ?', [$lowercaseWord])->first();
                    if (!$selectKecamatan) {
                        $messages[] = 'Kecamatan dengan nama ' . $item[2] . ' tidak ditemukan';
                        continue;
                    }
    
                    $ruas_jalan = ModelsRuasJalan::create([
                        'no_ruas' => $item[0],
                        'nama' => $item[1],
                        'panjang_ruas' => $item[3],
                        'lebar' => $item[4],
                        'kecamatan' => $selectKecamatan->id,
                        'akses' => $item[15]
                    ]);
    
                    //insert jenis_perkerasan
                    $jenis_perkerasan = JenisPerkerasan::create([
                        'ruas_jalan_id' => $ruas_jalan->id,
                        'rigit' => $item[5],
                        'hotmix' => $item[6],
                        'lapen' => $item[7],
                        'telford' => $item[8],
                        'tanah' => $item[9],
                        'tahun' => date('Y'),
                        'baik' => $item[10],
                        'sedang' => $item[11],
                        'rusak_ringan' => $item[12],
                        'rusak_berat' => $item[13]
                    ]);
                }
                FacadesDB::commit();
            } catch (Exception $e) {
                FacadesDB::rollBack();
                $messages[] = 'Terjadi kesalahan saat memproses transaksi: ' . $e->getMessage();
            }
        }
        return response()->json(['messages' => $messages]);

        // 0 => "102F1"
        // 1 => "JALAN MAYJEN. H.M. Sarbini (BANDAR LAMPUNG) IIIVX update"
        // 2 => "Gunung Agung"
        // 3 => 3.315 panjang
        // 4 => 7 lebar
        // 5 => 6001.0 
        // 6 => 5001.0
        // 7 => 7001.0
        // 8 => 8001.0
        // 9 => 10001.0
        // 10 => 10001.0
        // 11 => 10001.0
        // 12 => 10001.0
        // 13 => 10001.0
        // 14 => "-"
        // 15 => "akses 1"
        // 16 => "-"
    }
}
