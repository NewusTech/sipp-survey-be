<?php

namespace App\Http\Controllers\API\Desa;

use App\Http\Controllers\Controller;
use App\Models\DesaModel;
use Exception;
use Illuminate\Http\Request;

class DesaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $data = DesaModel::select(
                'master_desa.id', 
                'master_desa.nama',
                'kecamatan.id as kecamatan_id',
                'kecamatan.name as kecamatan_name',
                'master_desa.created_at'
            )
            ->leftjoin('kecamatan','kecamatan.id','=','master_desa.kecamatan_id')
            ->latest();
            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $data->where(function ($query) use ($searchTerm) {
                    $query->where('master_desa.nama', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('kecamatan_id') && $request->input('kecamatan_id')) {
                $kecamatan_id = $request->input('kecamatan_id');
                $data->where('master_desa.kecamatan_id', $kecamatan_id);
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
}
