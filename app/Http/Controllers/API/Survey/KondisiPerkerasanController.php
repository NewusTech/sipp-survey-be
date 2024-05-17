<?php

namespace App\Http\Controllers\API\Survey;

use App\Http\Controllers\Controller;
use App\Models\KondisiPerkerasan;
use App\Models\RuasJalan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KondisiPerkerasanController extends Controller
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
            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $query->whereRaw('MONTH(kondisi_perkerasan.created_at) = ?', [$searchMonth]);
            }

            if ($request->has('koridor') && $request->input('koridor')) {
                $searchKoridor = $request->input('koridor'); //id_koridor
                $query->where('master_koridor.id', $searchKoridor);
            }

            if ($request->has('wilayah') && $request->input('wilayah')) {
                $searchWilayah = $request->input('wilayah'); //id_koridor
                $query->where('master_ruas_jalan.kabupaten', $searchWilayah);
            }

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
            $data = new KondisiPerkerasan();
            $data->ruas_jalan_id = $request->ruas_jalan_id;
            $where = ['id' =>  $request->ruas_jalan_id];
            $collection = RuasJalan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data->baik = $request->baik;
            $data->sedang = $request->sedang;
            $data->rusak_ringan = $request->rusak_ringan;
            $data->rusak_berat = $request->rusak_berat;
            $data->created_by = Auth::id();
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
            $data = KondisiPerkerasan::select(
                'kondisi_perkerasan.id',
                'kondisi_perkerasan.baik',
                'kondisi_perkerasan.sedang',
                'kondisi_perkerasan.rusak_ringan',
                'kondisi_perkerasan.rusak_berat'
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
            $collection = KondisiPerkerasan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }

            $data = KondisiPerkerasan::find($id);

            $data->baik = $request->baik;
            $data->sedang = $request->sedang;
            $data->rusak_ringan = $request->rusak_ringan;
            $data->rusak_berat = $request->rusak_berat;
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
            $collection = KondisiPerkerasan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = KondisiPerkerasan::find($id);
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

    public function LaporanList(Request $request)
    {
        try {
            $paginate_count = 10;
            $query = KondisiPerkerasan::leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','kondisi_perkerasan.ruas_jalan_id')
            ->leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
            ->select(
                'kondisi_perkerasan.ruas_jalan_id',
                'master_ruas_jalan.nama as nama_ruas',
                'master_koridor.id as id_koridor',
                'master_koridor.name as nama_koridor',
                'master_ruas_jalan.panjang_ruas',
                'kondisi_perkerasan.baik',
                'kondisi_perkerasan.sedang',
                'kondisi_perkerasan.rusak_ringan',
                'kondisi_perkerasan.rusak_berat',
                'kondisi_perkerasan.created_at'
            )->latest();
            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $query->whereRaw('MONTH(kondisi_perkerasan.created_at) = ?', [$searchMonth]);
            }

            if ($request->has('koridor') && $request->input('koridor')) {
                $searchKoridor = $request->input('koridor'); //id_koridor
                $query->where('master_koridor.id', $searchKoridor);
            }

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
}
