<?php

namespace App\Exports;

use App\Models\Jembatan;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class JembatanExport implements FromView
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $query = Jembatan::select(
            'jembatan.id',
            'jembatan.no_ruas',
            'jembatan.kecamatan_id',
            'kecamatan.name as kecamatan_name',
            'jembatan.nama_ruas',
            'jembatan.no_jembatan',
            'jembatan.asal',
            'jembatan.nama_jembatan',
            'jembatan.kmpost',
            'jembatan.panjang',
            'jembatan.lebar',
            'jembatan.jml_bentang',
            'jembatan.tipe_ba',
            'jembatan.kondisi_ba',
            'jembatan.tipe_bb',
            'jembatan.kondisi_bb',
            'jembatan.tipe_fondasi',
            'jembatan.kondisi_fondasi',
            'jembatan.bahan',
            'jembatan.kondisi_lantai',
            'jembatan.latitude',
            'jembatan.longitude',
            'jembatan.created_at',
            'jembatan.tahun',
        )->leftjoin('kecamatan', 'kecamatan.id', '=', 'jembatan.kecamatan_id')->latest();

        if ($this->tahun) {
            $query->where('jembatan.tahun', $this->tahun);
        }

        $data = $query->cursor();
        $results = [];
        foreach ($data as $item) {
            $nilai_kondisi = ((int) $item->kondisi_ba + (int) $item->kondisi_bb + (int) $item->kondisi_fondasi + (int) $item->kondisi_lantai) / 4;
            $kondisi = "";
            if ($nilai_kondisi <= 1) {
                $kondisi = "B";
            } elseif ($nilai_kondisi <= 2) {
                $kondisi = "S";
            } elseif ($nilai_kondisi <= 3) {
                $kondisi = "RR";
            } else {
                $kondisi = "RB";
            }

            $results[] = [
                'no_ruas'          => $item->no_ruas,
                'kecamatan_name'   => $item->kecamatan_name,
                'nama_ruas'        => $item->nama_ruas,
                'no_jembatan'      => $item->no_jembatan,
                'asal'             => $item->asal,
                'nama_jembatan'    => $item->nama_jembatan,
                'kmpost'           => $item->kmpost,
                'panjang'          => $item->panjang,
                'lebar'            => $item->lebar,
                'jml_bentang'      => $item->jml_bentang,
                'tipe_ba'          => $item->tipe_ba,
                'kondisi_ba'       => $item->kondisi_ba,
                'tipe_bb'          => $item->tipe_bb,
                'kondisi_bb'       => $item->kondisi_bb,
                'tipe_fondasi'     => $item->tipe_fondasi,
                'kondisi_fondasi'  => $item->kondisi_fondasi,
                'bahan'            => $item->bahan,
                'kondisi_lantai'   => $item->kondisi_lantai,
                'latitude'         => $item->latitude,
                'longitude'        => $item->longitude,
                'nilai_kondisi'    => $nilai_kondisi,
                'kondisi'          => $kondisi,
                'tahun'            => $item->tahun
            ];
        }
        return view('exports.jembatan', [
            'data' => $results
        ]);
    }
}
