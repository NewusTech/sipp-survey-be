<?php

namespace App\Http\Controllers\API\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DrainaseModel;
use App\Models\Jembatan;
use App\Models\JenisPerkerasan;
use App\Models\KondisiPerkerasan;
use App\Models\RuasJalan;
use App\Models\SurveyDrainaseModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $ruas = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.tahun',
                DB::raw('SUM(master_ruas_jalan.panjang_ruas) as panjang_ruas_count'),
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=', 'jenis_perkerasan.ruas_jalan_id')
            ->groupBy('jenis_perkerasan.id','jenis_perkerasan.created_at', 'jenis_perkerasan.tahun')
            ->latest();
            
            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $ruas->where('jenis_perkerasan.tahun', $tahun);
            }

            $data = $ruas->get();
            $panjang_ruas_count = 0;
            foreach ($data as $key => $item) {
                $panjang_ruas_count += $item->panjang_ruas_count;
            }
            $tot_panjang_ruas = $panjang_ruas_count;
            $tot_ruas = count($data);

            $dataTot['tot_panjang_jalan'] = $tot_panjang_ruas ? round($tot_panjang_ruas, 3) : "";
            $dataTot['jumlah_ruas'] = $tot_ruas ? round($tot_ruas, 3) : "";
            return response()->json([
                'success' => true,
                'data' => $dataTot,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function lokasi_jalan(Request $request)
    {
        try {
            $paginate_count = 10;
            $query = KondisiPerkerasan::leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','kondisi_perkerasan.ruas_jalan_id')
            ->leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
            ->select(
                'kondisi_perkerasan.id',
                'kondisi_perkerasan.ruas_jalan_id',
                'master_ruas_jalan.nama as nama_ruas',
                'master_koridor.id as id_koridor',
                'master_koridor.name as nama_koridor',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.no_ruas',
                'kondisi_perkerasan.baik',
                'kondisi_perkerasan.sedang',
                'kondisi_perkerasan.rusak_ringan',
                'kondisi_perkerasan.rusak_berat',
                'kondisi_perkerasan.created_at'
            )->latest();

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }   

            $data = $query->paginate($paginate_count);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function barchart()
    {
        try {
        $currentYear = date('Y');
        $query = DB::table(DB::raw('(SELECT 1 as bulan UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) AS months'))
                ->leftJoin('kondisi_perkerasan', function($join) use ($currentYear) {
                    $join->on(DB::raw('MONTH(kondisi_perkerasan.created_at)'), '=', 'months.bulan')
                        ->whereYear('kondisi_perkerasan.created_at', '=', $currentYear);
                })
                ->selectRaw("months.bulan,
                            COALESCE(SUM(kondisi_perkerasan.baik), 0) as baik,
                            COALESCE(SUM(kondisi_perkerasan.sedang), 0) as sedang,
                            COALESCE(SUM(kondisi_perkerasan.rusak_ringan), 0) as rusak_ringan,
                            COALESCE(SUM(kondisi_perkerasan.rusak_berat), 0) as rusak_berat")
                ->groupBy('months.bulan')
                ->orderBy('months.bulan')
                ->get()->toArray();

            return response()->json([
                'success' => true,
                'data' => $query,
                'message' => 'Berhasil get data'
            ]); 

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function kemantapan(Request $request)
    {
        try {
            $jenis_perkerasan = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                DB::raw('SUM(jenis_perkerasan.baik) as baik_count'),
                DB::raw('SUM(jenis_perkerasan.sedang) as sedang_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_ringan) as rusak_ringan_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_berat) as rusak_berat_count'),
                DB::raw('SUM(master_ruas_jalan.panjang_ruas) as panjang_ruas_count'),
                'jenis_perkerasan.created_at',
            )
            ->groupBy('jenis_perkerasan.id','jenis_perkerasan.created_at')
            ->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->latest();
            
            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $jenis_perkerasan->where('jenis_perkerasan.tahun', $tahun);
            }

            $jenis_perkerasan  = $jenis_perkerasan->get();
            $baik_count          = 0;
            $sedang_count        = 0;
            $rusak_ringan_count  = 0;
            $rusak_berat_count   = 0;
            $panjang_ruas_count  = 0;
                foreach ($jenis_perkerasan as $key => $item) {
                    $baik_count += $item->baik_count;
                    $sedang_count += $item->sedang_count;
                    $rusak_ringan_count += $item->rusak_ringan_count;
                    $rusak_berat_count += $item->rusak_berat_count;
                    $panjang_ruas_count  += $item->panjang_ruas_count;
                }
            //kemantapan
            if ($panjang_ruas_count != 0) {
                $mantap_percentage = ($baik_count + $sedang_count) / $panjang_ruas_count * 100;
                $tmantap_percentage = ($rusak_ringan_count + $rusak_berat_count) / $panjang_ruas_count * 100;
            }else{
                $mantap_percentage = 0;
                $tmantap_percentage = 0;
            }

            $resp['mantap'] = round($mantap_percentage, 3);
            $resp['tmantap'] = round($tmantap_percentage, 3);

            return response()->json([
                'success' => true,
                'data' => $resp,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function maps(Request $request)
    {
        try {
            $data = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.baik',
                'jenis_perkerasan.sedang',
                'jenis_perkerasan.rusak_ringan',
                'jenis_perkerasan.rusak_berat',
                'master_ruas_jalan.nama as ruas_jalan',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.latitude',
                'master_ruas_jalan.longitude',
                'jenis_perkerasan.created_at',
                'kecamatan.name as kecamatan'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->whereYear('master_ruas_jalan.created_at', $tahun);
            }

            $data = $data->cursor();
            $results = [];
            foreach ($data as $query) {
                $mantap = ($query->baik + $query->sedang) / $query->panjang_ruas * 100;
                $tmantap = ($query->rusak_ringan + $query->rusak_berat) / $query->panjang_ruas * 100;
                $results[] = [
                    'id' => $query->id,
                    'baik' => $this->number_format($query->baik),
                    'sedang' => $this->number_format($query->sedang),
                    'rusak_ringan' => $this->number_format($query->rusak_ringan),
                    'rusak_berat' => $this->number_format($query->rusak_berat),
                    'ruas_jalan' => $query->ruas_jalan,
                    'panjang_ruas' => $this->number_format($query->panjang_ruas),
                    'latitude' => $query->latitude,
                    'longitude' => $query->longitude,
                    'created_at' => $query->created_at,
                    'kecamatan' => $query->kecamatan,
                    "mantap" => $this->number_format($mantap),
                    "tmantap" => $this->number_format($tmantap)
                ];
            }
            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function piechart(Request $request)
    {
        try {
            $data = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.created_at',
                'jenis_perkerasan.tahun',
                DB::raw('SUM(jenis_perkerasan.baik) as baik_count'),
                DB::raw('SUM(jenis_perkerasan.sedang) as sedang_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_ringan) as rusak_ringan_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_berat) as rusak_berat_count'),
            )->groupBy('jenis_perkerasan.id','jenis_perkerasan.created_at','jenis_perkerasan.tahun')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->where('jenis_perkerasan.tahun', $tahun);
            }

            $data = $data->get();
            $baik_count = 0;
            $sedang_count = 0;
            $rusak_ringan_count = 0;
            $rusak_berat_count = 0;
            foreach ($data as $key => $item) {
                $baik_count += $item->baik_count;
                $sedang_count += $item->sedang_count;
                $rusak_ringan_count += $item->rusak_ringan_count;
                $rusak_berat_count += $item->rusak_berat_count;
            }

            $baik_count = $baik_count;
            $sedang_count = $sedang_count;
            $rusak_ringan_count = $rusak_ringan_count;
            $rusak_berat_count = $rusak_berat_count;

            $dataTot['baik'] = $baik_count ? round($baik_count, 3) : "";
            $dataTot['sedang'] = $sedang_count ? round($sedang_count, 3) : "";
            $dataTot['rusak_ringan'] = $rusak_ringan_count ? round($rusak_ringan_count, 3) : "";
            $dataTot['rusak_berat'] = $rusak_berat_count ? round($rusak_berat_count, 3) : "";
            $dataTot['tahun'] = count($data) != 0 ? $data[0]->tahun : "";

            return response()->json([
                'success' => true,
                'data' => $dataTot,
                'message' => 'Berhasil get data'
            ]); 

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function maps_jembatan(Request $request)
    {
        try {
            $data = Jembatan::select(
                'jembatan.id',
                'jembatan.tahun',
                'jembatan.nama_ruas',
                'jembatan.nama_jembatan',
                'jembatan.latitude',
                'jembatan.longitude',
                'jembatan.kondisi_ba',
                'jembatan.kondisi_bb',
                'jembatan.kondisi_fondasi',
                'jembatan.kondisi_lantai',
                'kecamatan.name as kecamatan',
                'jembatan.created_at'
            )->leftjoin('kecamatan','kecamatan.id','=','jembatan.kecamatan_id')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->where('jembatan.tahun', $tahun);
            }

            $data = $data->cursor();
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
                    'id'                => $item->id,
                    'tahun'             => $item->tahun,
                    'nama_ruas'         => $item->nama_ruas,
                    'nama_jembatan'     => $item->nama_jembatan,
                    'latitude'          => $item->latitude,
                    'longitude'         => $item->longitude,
                    'kondisi_ba'        => $item->kondisi_ba,
                    'kondisi_bb'        => $item->kondisi_bb,
                    'kondisi_fondasi'   => $item->kondisi_fondasi,
                    'kondisi_lantai'    => $item->kondisi_lantai,
                    'kecamatan'         => $item->kecamatan,
                    'nilai_kondisi'     => $nilai_kondisi,
                    'kondisi'           => $kondisi
                ];
            }
            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function detail_mantap(Request $request)
    {
        try {
            $data = RuasJalan::select(
                'master_ruas_jalan.id',
                'master_ruas_jalan.nama',
                'kecamatan.name as name_kecamatan',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.lebar',
                'jenis_perkerasan.baik',
                'jenis_perkerasan.sedang',
                'jenis_perkerasan.created_at'
            )->leftjoin('jenis_perkerasan','jenis_perkerasan.ruas_jalan_id','=','master_ruas_jalan.id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull(['jenis_perkerasan.baik','jenis_perkerasan.sedang'])
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->where('jenis_perkerasan.tahun', $tahun);
            }

            $resp = $data->cursor();
            $results = [];
            foreach ($resp as $query) {
                $results[] = [
                    "id" => $query->id,
                    "nama" => $query->nama,
                    "name_kecamatan" => $query->name_kecamatan,
                    "panjang_ruas" => $this->number_format($query->panjang_ruas),
                    "lebar" => $this->number_format($query->lebar),
                    "baik" => $this->number_format($query->baik),
                    "sedang" => $this->number_format($query->sedang),
                    "created_at" => $query->created_at
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function detail_tmantap(Request $request)
    {
        try {
            $data = RuasJalan::select(
                'master_ruas_jalan.id',
                'master_ruas_jalan.nama',
                'kecamatan.name as name_kecamatan',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.lebar',
                'jenis_perkerasan.rusak_ringan',
                'jenis_perkerasan.rusak_berat',
                'jenis_perkerasan.created_at'
            )->leftjoin('jenis_perkerasan','jenis_perkerasan.ruas_jalan_id','=','master_ruas_jalan.id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull(['jenis_perkerasan.rusak_ringan','jenis_perkerasan.rusak_berat'])
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->where('jenis_perkerasan.tahun', $tahun);
            }

            $resp = $data->cursor();
            $results = [];
            foreach ($resp as $query) {
                $results[] = [
                    "id" => $query->id,
                    "nama" => $query->nama,
                    "name_kecamatan" => $query->name_kecamatan,
                    "panjang_ruas" => $this->number_format($query->panjang_ruas),
                    "lebar" => $this->number_format($query->lebar),
                    "rusak_ringan" => $this->number_format($query->rusak_ringan),
                    "rusak_berat" => $this->number_format($query->rusak_berat),
                    "created_at" => $query->created_at
                ];
            }
            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function drainase(Request $request)
    {
        try {
            $data = DrainaseModel::select(
                DB::raw('SUM(drainase.panjang_ruas) as total_panjang_ruas'), 
                DB::raw('COUNT(drainase.id) as jumlah_drainase')
            );

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->whereYear('drainase.created_at', $tahun);
            }
           
            $data = $data->first();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function maps_drainase(Request $request)
    {
        try {
            $data = SurveyDrainaseModel::select(
                'survey_drainase.id',
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
                'survey_drainase.latitude',
                'survey_drainase.longitude',
                'survey_drainase.created_at',
                'kecamatan.name as nama_kecamatan'
            )
            ->leftjoin('drainase','drainase.id','=','survey_drainase.ruas_drainase_id')
            ->leftjoin('master_desa','master_desa.id','=','drainase.desa_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_desa.kecamatan_id');

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->whereYear('drainase.created_at', $tahun);
            }
           
            $data = $data->get();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
