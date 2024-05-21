<?php

namespace App\Imports;

use App\Models\Jembatan;
use App\Models\Kecamatan;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\DB as FacadesDB;

class JembatanImport implements ToCollection
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
                $data[2]
            );
            $messages = [];
            try {
                foreach ($data as $key => $item) {
                    // check no_jembatan
                    // $noJembatan = Jembatan::where('no_jembatan', $item[3])->exists();
                    // if ($noJembatan) {
                    //     $messages[] = 'Data no_jembatan sudah ada, no_jembatan tidak boleh duplicate';
                    //     return;
                    // }
    
                    // check nama ruas
                    // $existNameRuas = Jembatan::where('nama_ruas', $item[2])->exists();
                    // if ($existNameRuas) {
                    //     $messages[] = 'Data nama ruas sudah ada, nama ruas tidak boleh duplicate';
                    //     return;
                    // }
    
                    // if ($noJembatan && $existNameRuas) {
                    //     $messages[] = 'gagal import, data tidak boleh sama';
                    //     return;
                    // }
                    $selectKecamatan = Kecamatan::where('name', $item[1])->first();
                    if (!$selectKecamatan) {
                        $messages[] = 'Kecamatan dengan nama ' . $item[1] . ' tidak ditemukan';
                        continue;
                    }
                    Jembatan::create([
                        'no_ruas' => $item[0],
                        'kecamatan_id' => $selectKecamatan->id,
                        'nama_ruas' => $item[2],
                        'no_jembatan' => $item[3],

                        'asal' => $item[4],
                        'nama_jembatan' => $item[5],
                        'kmpost' => $item[6],
                        'panjang' => $item[7],
                        'lebar' => $item[8],
                        'jml_bentang' => $item[9],
                        'tipe_ba' => $item[10],
                        'kondisi_ba' => $item[11],
                        'tipe_bb' => $item[12],
                        'kondisi_bb' => $item[13],
                        'tipe_fondasi' => $item[14],
                        'kondisi_fondasi' => $item[15],
                        'bahan' => $item[16],
                        'kondisi_lantai' => $item[17],
                        'latitude' => $item[18],
                        'longitude' => $item[19],
                        'created_by' => Auth::user()->id,
                        'tahun' => date('Y')
                    ]);
    
                }
            } catch (Exception $e) {
                $messages[] = 'Terjadi kesalahan saat memproses transaksi: ' . $e->getMessage();
            }
        }
        return response()->json(['messages' => $messages]);
    }

    // #items: array:22 [
    //     0 => "00039NW"
    //     1 => "Batu Putih"
    //     2 => "SP. PENUMANGAN BARU - PENUMANGAN BARU NEWN"
    //     3 => "18.12.001.1"
    //     4 => "SP. PENUMANGAN BARU"
    //     5 => "WAY PENUMANGAN BARU"
    //     6 => "0+916"
    //     7 => 3.1
    //     8 => 6.4
    //     9 => 1
    //     10 => "O"
    //     11 => 1
    //     12 => "LS"
    //     13 => 1
    //     14 => "LS"
    //     15 => 1
    //     16 => "T"
    //     17 => 1
    //     18 => "4 29 54.6"
    //     19 => "105 08 59.0"
    //     20 => 1
    //     21 => "B"
    //   ]
}
