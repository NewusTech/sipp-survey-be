<?php

namespace App\Http\Controllers\API\Drainase;

use App\Http\Controllers\Controller;
use App\Models\DrainaseModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DrainaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $paginate_count = 10;
            $query = DrainaseModel::select(
                'drainase.id',
                'drainase.nama_ruas',
                'drainase.panjang_ruas',
                'drainase.desa_id',
                'master_desa.nama as nama_desa',
                'kecamatan.name as nama_kecamatan',
                'drainase.created_at'
            )->leftjoin('master_desa','master_desa.id','=','drainase.desa_id')
            ->leftjoin('kecamatan', 'kecamatan.id','=','master_desa.kecamatan_id')
            ->latest();

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('drainase.nama_ruas', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->whereYear('drainase.created_at', $tahun);
            }

            if ($request->has('kecamatan_id') && $request->input('kecamatan_id')) {
                $kecamatan_id = $request->input('kecamatan_id');
                $query->where('master_desa.kecamatan_id', $kecamatan_id);
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
            $validator = Validator::make($request->all(), [
                'nama_ruas' => 'required|unique:drainase'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 500);
            }

            $data = new DrainaseModel();
            $data->nama_ruas              = $request->nama_ruas;
            $data->panjang_ruas           = $request->panjang_ruas;
            $data->desa_id                = $request->desa_id;

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
            $query = DrainaseModel::select(
                'drainase.id',
                'drainase.nama_ruas',
                'drainase.panjang_ruas',
                'drainase.desa_id',
                'master_desa.nama as nama_desa',
                'kecamatan.name as nama_kecamatan',
                'drainase.created_at'
            )->leftjoin('master_desa','master_desa.id','=','drainase.desa_id')
            ->leftjoin('kecamatan', 'kecamatan.id','=','master_desa.kecamatan_id')
            ->find($id);

            return response()->json([
                'success' => true,
                'data' => $query,
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
            $collection = DrainaseModel::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }

            $data = DrainaseModel::find($id);
            $data->nama_ruas              = $request->nama_ruas;
            $data->panjang_ruas           = $request->panjang_ruas;
            $data->desa_id                = $request->desa_id;

            $data->save();

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
            $collection = DrainaseModel::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = DrainaseModel::find($id);
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
}
