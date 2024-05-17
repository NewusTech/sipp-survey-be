<?php

namespace App\Exports;

use App\Models\JenisPerkerasan;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class SurveyByRowExport implements FromView
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

        $data = $query->get();
        return view('exports.survey', [
            'survey' => $data
        ]);
    }
}
