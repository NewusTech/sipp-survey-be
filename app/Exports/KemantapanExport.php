<?php

namespace App\Exports;

use App\Models\JenisPerkerasan;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class KemantapanExport implements FromView
{
    protected $tahun;
    protected $id;

    public function __construct($tahun, $id)
    {
        $this->tahun = $tahun;
        $this->id = $id;
    }

    public function view(): View
    {
        $query = JenisPerkerasan::leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
        ->leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
        ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
        ->select(
            'master_ruas_jalan.no_ruas',
            'master_ruas_jalan.nama as nama_ruas',
            'kecamatan.name as name_kecamatan',
            'master_ruas_jalan.panjang_ruas',
            'master_ruas_jalan.lebar',
            'jenis_perkerasan.hotmix',
            'jenis_perkerasan.rigit',
            'jenis_perkerasan.lapen',
            'jenis_perkerasan.telford',
            'jenis_perkerasan.tanah',
            'jenis_perkerasan.baik',
            'jenis_perkerasan.sedang',
            'jenis_perkerasan.rusak_ringan',
            'jenis_perkerasan.rusak_berat',
            'master_ruas_jalan.akses',
            'jenis_perkerasan.tahun',
            'jenis_perkerasan.created_at',
            'jenis_perkerasan.lhr',
            'jenis_perkerasan.keterangan'
        )
        ->latest();

        if ($this->tahun) {
            $query->where('jenis_perkerasan.tahun', $this->tahun);
        }

        if ($this->id) {
            $query->whereIn('jenis_perkerasan.id', $this->id);
        }

        $data = $query->cursor();
        $results = [];
        foreach ($data as $query) {
            $mantap = number_format((($query->baik + $query->sedang) / $query->panjang_ruas) * 100, 3);
            $tmantap = number_format((($query->rusak_ringan + $query->rusak_berat) / $query->panjang_ruas) * 100, 3);
            $results[] = [
                "id" => $query->id,
                "ruas_jalan_id" => $query->ruas_jalan_id,
                "nama_ruas" => $query->nama_ruas,
                "id_koridor" => $query->id_koridor,
                "nama_koridor" => $query->nama_koridor,
                "panjang_ruas" => $query->panjang_ruas ? $this->number_format($query->panjang_ruas) : '',
                "no_ruas" => $query->no_ruas,
                "lebar" => $this->number_format($query->lebar),
                "akses" => $query->akses,
                "rigit" => $this->number_format($query->rigit),
                "hotmix" => $this->number_format($query->hotmix),
                "lapen" => $this->number_format($query->lapen),
                "telford" => $this->number_format($query->telford),
                "tanah" => $this->number_format($query->tanah),
                "tahun" => $query->tahun,
                "baik" => $this->number_format($query->baik),
                "sedang" => $this->number_format($query->sedang),
                "rusak_ringan" => $this->number_format($query->rusak_ringan),
                "rusak_berat" => $this->number_format($query->rusak_berat),
                "created_at" => $query->created_at,
                "name_kecamatan" =>$query->name_kecamatan,
                "lhr" =>$query->lhr,
                "keterangan" =>$query->keterangan,
                "mantap" => $mantap,
                "tmantap" => $tmantap
            ];
        }
        return view('exports.kemantapan', [
            'survey' => $results
        ]);
    }

    public function number_format($angka)
    {
        $angka = floatval($angka);
        $formatted_number = number_format($angka, 2);

        if (strpos($formatted_number, ".00") !== false) {
            $formatted_number = rtrim($formatted_number, "0");
            $formatted_number = rtrim($formatted_number, ".");
        }

        return $formatted_number;
    }
}
