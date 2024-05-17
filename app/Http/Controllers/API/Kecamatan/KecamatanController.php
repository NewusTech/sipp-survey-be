<?php

namespace App\Http\Controllers\API\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Kecamatan;
use Exception;
use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $data = Kecamatan::select('id', 'name')->orderBy('id','ASC');
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
