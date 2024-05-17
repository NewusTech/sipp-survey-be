<?php

namespace App\Exports;

use App\Models\DrainaseModel;
use App\Models\SurveyDrainaseModel;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class SurveyDrainaseExport implements FromView
{
    protected $tahun;
    protected $desa_id;
    // protected $id_ruas_drainase;

    public function __construct($tahun, $desa_id)
    {
        $this->tahun    = $tahun;
        $this->desa_id  = $desa_id;
        // $this->id_ruas_drainase       = $id_ruas_drainase;
    }

    public function view(): View
    {
        $query = SurveyDrainaseModel::select(
            'survey_drainase.id',
            'drainase.id as id_drainase',
            'drainase.nama_ruas',
            'drainase.panjang_ruas',
            'drainase.desa_id',
            'master_desa.nama as nama_desa',
            'survey_drainase.panjang_drainase',
            'survey_drainase.letak_drainase',
            'survey_drainase.lebar_atas',
            'survey_drainase.lebar_bawah',
            'survey_drainase.tinggi',
            'survey_drainase.kondisi',
            'survey_drainase.created_at',
            'kecamatan.name as nama_kecamatan'
        )
            ->leftjoin('drainase', 'drainase.id', '=', 'survey_drainase.ruas_drainase_id')
            ->leftjoin('master_desa', 'master_desa.id', '=', 'drainase.desa_id')
            ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_desa.kecamatan_id')
            ->latest();

        if ($this->desa_id) {
            $query->where('drainase.desa_id', $this->desa_id);
        }

        // if ($this->id_ruas_drainase) {
        //     $query->whereIn('drainase.id', [2]);
        // }

        if ($this->tahun) {
            $query->whereYear('survey_drainase.created_at', $this->tahun);
        }

        $data = $query->get();

        $total_panjang_ruas = DrainaseModel::select(DB::raw('SUM(drainase.panjang_ruas) as total_panjang_ruas'));

        $total_panjang_ruas = $total_panjang_ruas->first();

        $total_panjang_drainase = SurveyDrainaseModel::selectRaw('SUM(survey_drainase.panjang_drainase) as total_panjang_drainase')
            ->leftjoin('drainase', 'drainase.id', '=', 'survey_drainase.ruas_drainase_id')
            ->leftjoin('master_desa', 'master_desa.id', '=', 'drainase.desa_id')
            ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_desa.kecamatan_id')
            ->where('drainase.desa_id', $this->desa_id)
            ->first();

        if ($total_panjang_drainase) {
            if ($total_panjang_drainase->total_panjang_drainase) {
                $total_panjang = $total_panjang_drainase->total_panjang_drainase;
                $total_panjang_drainase = $total_panjang;
                $data_total_panjang_ruas = $total_panjang_ruas->total_panjang_ruas;
                $data_total_panjang_drainase = $total_panjang_drainase;
                $data_total_panjang_drainase_kondisi_tanah = (int) $total_panjang_ruas->total_panjang_ruas - (int) $total_panjang_drainase;
            }
        }

        return view('exports.survey-drainase', [
            'survey' => $data,
            'data_total_panjang_ruas' => $total_panjang_drainase ? $data_total_panjang_ruas : '-',
            'data_total_panjang_drainase' => $total_panjang_drainase ? $data_total_panjang_drainase : '-',
            'data_total_panjang_drainase_kondisi_tanah' => $total_panjang_drainase ?  $data_total_panjang_drainase_kondisi_tanah : '-',
        ]);
    }
}
