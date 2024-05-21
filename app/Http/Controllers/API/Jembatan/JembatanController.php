<?php

namespace App\Http\Controllers\API\Jembatan;

use App\Exports\JembatanByRowExport;
use App\Http\Controllers\Controller;
use App\Models\Jembatan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JembatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $paginate_count = 10;
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
            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('jembatan.nama_ruas', 'like', '%' . $searchTerm . '%')
                        ->orWhere('jembatan.nama_jembatan', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jembatan.tahun', $tahun);
            }

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $query->whereRaw('MONTH(jembatan.created_at) = ?', [$searchMonth]);
            }

            $data = $query->paginate($paginate_count);

            $resdata = $data->getCollection()->map(function ($item) {
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

                return [
                    'id'               => $item->id,
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
                    'tahun'            => $item->tahun,
                    'created_at'       => $item->created_at
                ];
            });

            $data->setCollection($resdata);
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
            $validator = Validator::make($request->all(), [
                'no_ruas' => 'required|unique:jembatan',
                'kecamatan_id' => 'required|numeric',
                'nama_ruas' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 500);
            }

            $data = new Jembatan();
            $data->no_ruas              = $request->no_ruas;
            $data->kecamatan_id         = $request->kecamatan_id;
            $data->nama_ruas            = $request->nama_ruas;
            $data->no_jembatan          = $request->no_jembatan;
            $data->asal                 = $request->asal;
            $data->nama_jembatan        = $request->nama_jembatan;
            $data->kmpost               = $request->kmpost;
            $data->panjang              = $request->panjang;
            $data->lebar                = $request->lebar;
            $data->jml_bentang          = $request->jml_bentang;
            $data->tipe_ba              = $request->tipe_ba;
            $data->kondisi_ba           = $request->kondisi_ba;
            $data->tipe_bb              = $request->tipe_bb;
            $data->kondisi_bb           = $request->kondisi_bb;
            $data->tipe_fondasi         = $request->tipe_fondasi;
            $data->kondisi_fondasi      = $request->kondisi_fondasi;
            $data->bahan                = $request->bahan;
            $data->kondisi_lantai       = $request->kondisi_lantai;
            $data->latitude             = $request->latitude;
            $data->longitude            = $request->longitude;
            $data->created_by           = Auth::user()->id;
            $data->tahun                = date('Y');

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
            $where = ['id' => $id];
            $collection = Jembatan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }

            $data = Jembatan::select(
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
                'jembatan.tahun'
            )->leftjoin('kecamatan', 'kecamatan.id', '=', 'jembatan.kecamatan_id')
            ->find($id);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $nilai_kondisi = (($data->kondisi_ba + $data->kondisi_bb + $data->kondisi_fondasi + $data->kondisi_lantai) / 4);
            if ($nilai_kondisi <= 1) {
                $kondisi = "B";
            } elseif ($nilai_kondisi <= 2) {
                $kondisi = "S";
            } elseif ($nilai_kondisi <= 3) {
                $kondisi = "RR";
            } else {
                $kondisi = "RB";
            }

            $data->nilai_kondisi = $nilai_kondisi;
            $data->kondisi = $kondisi;

            $data = $data->toArray();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil menampilkan data'
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
            $collection = Jembatan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }

            $data = Jembatan::find($id);

            $data->kecamatan_id         = $request->kecamatan_id;
            $data->nama_ruas            = $request->nama_ruas;
            $data->no_jembatan          = $request->no_jembatan;
            $data->asal                 = $request->asal;
            $data->nama_jembatan        = $request->nama_jembatan;
            $data->kmpost               = $request->kmpost;
            $data->panjang              = $request->panjang;
            $data->lebar                = $request->lebar;
            $data->jml_bentang          = $request->jml_bentang;
            $data->tipe_ba              = $request->tipe_ba;
            $data->kondisi_ba           = $request->kondisi_ba;
            $data->tipe_bb              = $request->tipe_bb;
            $data->kondisi_bb           = $request->kondisi_bb;
            $data->tipe_fondasi         = $request->tipe_fondasi;
            $data->kondisi_fondasi      = $request->kondisi_fondasi;
            $data->bahan                = $request->bahan;
            $data->kondisi_lantai       = $request->kondisi_lantai;
            $data->latitude             = $request->latitude;
            $data->longitude            = $request->longitude;
            $data->no_ruas              = $request->no_ruas;
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
            $collection = Jembatan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = Jembatan::find($id);
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

    public function exportByRow(Request $request)
    {
        try {
            $year = $request->year;
            $id_jembatan = $request->id_jembatan;
            $excelFileName = 'jembatan-row-' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';
            if (!$id_jembatan) {
                return response()->json([
                    'success' => false,
                    'message' => 'id_jembatan tidak boleh kosong.',
                    'data' => null
                ]);
            }
            $excel = Excel::download(new JembatanByRowExport($year, $id_jembatan), $excelFileName);
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
}
