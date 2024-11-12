<?php

namespace App\Http\Controllers\API\Survey;

use App\Exports\SurveyByRowExport;
use App\Exports\SurveyExport;
use App\Http\Controllers\Controller;
use App\Models\JenisPerkerasan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class JenisPerkerasanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $paginate_count = 10;
            $query = JenisPerkerasan::leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->leftjoin('master_koridor', 'master_koridor.id', '=', 'master_ruas_jalan.koridor_id')
                ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_ruas_jalan.kecamatan')
                ->select(
                    'jenis_perkerasan.id',
                    'jenis_perkerasan.ruas_jalan_id',
                    'master_ruas_jalan.nama as nama_ruas',
                    'master_koridor.id as id_koridor',
                    'master_koridor.name as nama_koridor',
                    'master_ruas_jalan.panjang_ruas',
                    'master_ruas_jalan.no_ruas',
                    'master_ruas_jalan.lebar',
                    'master_ruas_jalan.akses',
                    'master_ruas_jalan.status',
                    'master_ruas_jalan.alasan',
                    'jenis_perkerasan.rigit',
                    'jenis_perkerasan.hotmix',
                    'jenis_perkerasan.lapen',
                    'jenis_perkerasan.telford',
                    'jenis_perkerasan.tanah',
                    'jenis_perkerasan.tahun',
                    'jenis_perkerasan.baik',
                    'jenis_perkerasan.sedang',
                    'jenis_perkerasan.rusak_ringan',
                    'jenis_perkerasan.rusak_berat',
                    'jenis_perkerasan.created_at',
                    'kecamatan.name as name_kecamatan',
                    'jenis_perkerasan.lhr',
                    'jenis_perkerasan.keterangan'
                )
                ->latest();

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $query->whereRaw('MONTH(jenis_perkerasan.created_at) = ?', [$searchMonth]);
            }

            if ($request->has('koridor') && $request->input('koridor')) {
                $searchKoridor = $request->input('koridor'); //id_koridor
                $query->where('master_koridor.id', $searchKoridor);
            }

            if ($request->has('wilayah') && $request->input('wilayah')) {
                $searchWilayah = $request->input('wilayah'); //id_koridor
                $query->where('master_ruas_jalan.kabupaten', $searchWilayah);
            }

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }

            if ($request->has('kecamatan_id') && $request->input('kecamatan_id')) {
                $filterKecamatan = $request->input('kecamatan_id');
                $query->where('master_ruas_jalan.kecamatan', $filterKecamatan);
            }

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            $data = $query->paginate($paginate_count);
            $resData = $data->getCollection()->map(function ($query) {
                $mantap = number_format((($query->baik + $query->sedang) / $query->panjang_ruas) * 100, 3);
                $tmantap = number_format((($query->rusak_ringan + $query->rusak_berat) / $query->panjang_ruas) * 100, 3);
                return [
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
                    "name_kecamatan" => $query->name_kecamatan,
                    "lhr" => $query->lhr,
                    "keterangan" => $query->keterangan,
                    "status" => $query->status,
                    "alasan" => $query->alasan,
                    "mantap"            => $mantap, // ((baik+sedang)/panjang_ruas) * 100)
                    "tmantap"           => $tmantap, //  ((rusak_ringan+rusak_berat) * 100)
                ];
            });

            $data->setCollection($resData);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = new JenisPerkerasan();
            $data->ruas_jalan_id = $request->ruas_jalan_id;
            $data->rigit = $request->rigit;
            $data->hotmix = $request->hotmix;
            $data->lapen = $request->lapen;
            $data->telford = $request->telford;
            $data->tanah = $request->tanah;
            $data->baik = $request->baik;
            $data->sedang = $request->sedang;
            $data->rusak_ringan = $request->rusak_ringan;
            $data->rusak_berat = $request->rusak_berat;

            // $data->agregat = $request->agregat;
            // $data->onderlagh = $request->onderlagh;
            $data->created_by = Auth::id();
            $data->tahun = date('Y');

            $data->lhr = $request->lhr;
            $data->keterangan = $request->keterangan;
            $data->save();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil create data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = JenisPerkerasan::leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->leftjoin('master_koridor', 'master_koridor.id', '=', 'master_ruas_jalan.koridor_id')
                ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_ruas_jalan.kecamatan')
                ->select(
                    'jenis_perkerasan.id',
                    'jenis_perkerasan.ruas_jalan_id',
                    'master_ruas_jalan.nama as nama_ruas',
                    'master_koridor.id as id_koridor',
                    'master_koridor.name as nama_koridor',
                    'master_ruas_jalan.panjang_ruas',
                    'master_ruas_jalan.no_ruas',
                    'master_ruas_jalan.lebar',
                    'master_ruas_jalan.akses',
                    'master_ruas_jalan.status',
                    'master_ruas_jalan.alasan',
                    'jenis_perkerasan.rigit',
                    'jenis_perkerasan.hotmix',
                    'jenis_perkerasan.lapen',
                    'jenis_perkerasan.telford',
                    'jenis_perkerasan.tanah',
                    'jenis_perkerasan.tahun',
                    'jenis_perkerasan.baik',
                    'jenis_perkerasan.sedang',
                    'jenis_perkerasan.rusak_ringan',
                    'jenis_perkerasan.rusak_berat',
                    'jenis_perkerasan.created_at',
                    'jenis_perkerasan.lhr',
                    'jenis_perkerasan.keterangan',
                    'kecamatan.name as name_kecamatan'
                )->find($id);

            if (!$data) {
                return response()->json(['error' => 'ID tidak ditemukan']);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil show data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $where = ['id' => $id];
            $collection = JenisPerkerasan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }

            $data = JenisPerkerasan::find($id);

            $data->ruas_jalan_id = $request->ruas_jalan_id;
            $data->rigit = $request->rigit;
            $data->hotmix = $request->hotmix;
            $data->lapen = $request->lapen;
            $data->telford = $request->telford;
            $data->tanah = $request->tanah;
            $data->baik = $request->baik;
            $data->sedang = $request->sedang;
            $data->rusak_ringan = $request->rusak_ringan;
            $data->rusak_berat = $request->rusak_berat;
            $data->lhr = $request->lhr;
            $data->keterangan = $request->keterangan;
            $data->update();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil update data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $where = ['id' => $id];
            $collection = JenisPerkerasan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = JenisPerkerasan::find($id);
            $data->delete();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil delete data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function LaporanIndex(Request $request)
    {
        try {
            $paginate_count = 10;
            $query = JenisPerkerasan::leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->leftjoin('master_koridor', 'master_koridor.id', '=', 'master_ruas_jalan.koridor_id')
                ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_ruas_jalan.kecamatan')
                ->select(
                    'jenis_perkerasan.id',
                    'jenis_perkerasan.ruas_jalan_id',
                    'master_ruas_jalan.nama as nama_ruas',
                    'master_koridor.id as id_koridor',
                    'master_koridor.name as nama_koridor',
                    'master_ruas_jalan.panjang_ruas',
                    'master_ruas_jalan.no_ruas',
                    'master_ruas_jalan.lebar',
                    'master_ruas_jalan.akses',
                    'jenis_perkerasan.rigit',
                    'jenis_perkerasan.hotmix',
                    'jenis_perkerasan.lapen',
                    'jenis_perkerasan.telford',
                    'jenis_perkerasan.tanah',
                    'jenis_perkerasan.tahun',
                    'jenis_perkerasan.baik',
                    'jenis_perkerasan.sedang',
                    'jenis_perkerasan.rusak_ringan',
                    'jenis_perkerasan.rusak_berat',
                    'jenis_perkerasan.created_at',
                    'jenis_perkerasan.lhr',
                    'jenis_perkerasan.keterangan',
                    'master_ruas_jalan.status',
                    'master_ruas_jalan.alasan'
                )
                ->latest();

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $query->whereRaw('MONTH(jenis_perkerasan.created_at) = ?', [$searchMonth]);
            }

            if ($request->has('koridor') && $request->input('koridor')) {
                $searchKoridor = $request->input('koridor'); //id_koridor
                $query->where('master_koridor.id', $searchKoridor);
            }

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            $data = $query->paginate($paginate_count);
            $resData = $data->getCollection()->map(function ($query) {
                return [
                    "id" => $query->id,
                    "ruas_jalan_id" => $query->ruas_jalan_id,
                    "nama_ruas" => $query->nama_ruas,
                    "id_koridor" => $query->id_koridor,
                    "nama_koridor" => $query->nama_koridor,
                    "panjang_ruas" => $this->number_format($query->panjang_ruas),
                    "no_ruas" => $query->no_ruas,
                    "lebar" => $query->lebar,
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
                    "name_kecamatan" => $query->name_kecamatan,
                    "lhr" => $query->lhr,
                    "keterangan" => $query->keterangan
                ];
            });

            $data->setCollection($resData);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportByRow(Request $request)
    {
        try {
            $year = $request->year;
            $id_survey = $request->id_survey;
            $excelFileName = 'survey-jalan-row' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';

            $excel = Excel::download(new SurveyByRowExport($year, $id_survey), $excelFileName);
            $filePath = $excel->getFile()->getPathname();
            $storagePath = 'public/exports';
            Storage::putFileAs($storagePath, $filePath, $excelFileName);
            $fileUrl = Storage::url($storagePath . '/' . $excelFileName);
            return response()->json([
                'success' => true,
                'message' => 'Survey exported successfully.',
                'file' => $excelFileName,
                'file_url' => url($fileUrl)
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
