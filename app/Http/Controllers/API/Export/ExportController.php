<?php

namespace App\Http\Controllers\API\Export;

use App\Exports\JembatanExport;
use App\Exports\SurveyExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function export_excel(Request $request)
    {
        $excelFileName = 'survey-jalan-' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';

        $excel = Excel::download(new SurveyExport($request->year), $excelFileName);
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
    }

    public function export_jembatan(Request $request)
    {
        $excelFileName = 'jembatan-' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';

        $excel = Excel::download(new JembatanExport($request->year), $excelFileName);
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
    }
}
