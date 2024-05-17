<?php

namespace App\Http\Controllers\API\Import;

use App\Http\Controllers\Controller;
use App\Imports\JembatanImport;
use App\Imports\RuasJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function import_ruas_jalan(Request $request)
    {
		$this->validate($request, [
			'file' => 'required|mimes:xls,xlsx'
		]);
		$file = $request->file('file');
		$nama_file = rand().$file->getClientOriginalName();
		$file->move('ruas_jalan',$nama_file);
		$dataImport = Excel::import(new RuasJalan, public_path('/ruas_jalan/'.$nama_file));
        $response = "File berhasil diunggah dan data berhasil diimpor.";
        if (!$dataImport) {
            $response = "Gagal mengimpor data. Silakan periksa format file Anda.";
        }
        return response()->json(['success' => true, 'message' => $response]);
    }

    public function import_jembatan(Request $request)
    {
        $this->validate($request, [
			'file' => 'required|mimes:xls,xlsx'
		]);
		$file = $request->file('file');
		$nama_file = rand().$file->getClientOriginalName();
		$file->move('jembatan_import',$nama_file);
		$dataImport = Excel::import(new JembatanImport, public_path('/jembatan_import/'.$nama_file));
        $response = "File berhasil diunggah dan data berhasil diimpor.";
        if (!$dataImport) {
            $response = "Gagal mengimpor data. Silakan periksa format file Anda.";
        }
        return response()->json(['success' => true, 'message' => $response]);
    }

    public function download_template($filename)
    {
        $filePath = public_path('template/' . $filename);
        if (file_exists($filePath)) {
            $fileUrl = url('template/' . $filename);
            return response()->json(['download_url' => $fileUrl]);
        } else {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }
}
