<?php

namespace App\Http\Controllers\API\Drainase;

use App\Exports\SurveyDrainaseExport;
use App\Http\Controllers\Controller;
use App\Models\DesaModel;
use App\Models\DrainaseModel;
use App\Models\SurveyDrainaseModel;
use App\Models\SurveyDrainasePhoto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;


class SurveyDrainaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try {
            $paginate_count = 10;
            $data = DesaModel::select(
                'master_desa.id',
                'master_desa.nama as nama_desa',
                'kecamatan.name as nama_kecamatan',
                DB::raw('SUM(drainase.panjang_ruas) as total_panjang_ruas')
            )->leftjoin('drainase','drainase.desa_id','=','master_desa.id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_desa.kecamatan_id')
            ->groupBy('master_desa.id','master_desa.nama','kecamatan.name');

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $data->where('master_desa.nama', 'like', '%' . $searchTerm . '%');
            }

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            $data = $data->paginate($paginate_count);
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
                'ruas_drainase_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 500);
            }

            $data = new SurveyDrainaseModel();
            $data->ruas_drainase_id = $request->ruas_drainase_id;
            $data->panjang_drainase = $request->panjang_drainase;
            $data->letak_drainase = $request->letak_drainase;
            $data->lebar_atas = $request->lebar_atas;
            $data->lebar_bawah = $request->lebar_bawah;
            $data->tinggi = $request->tinggi;
            $data->kondisi = $request->kondisi;
            $data->latitude = $request->latitude;
            $data->longitude = $request->longitude;
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
    public function show(Request $request)
    {
        try {
            $desa_id = $request->desa_id;
            $paginate_count = 10;
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
            ->leftjoin('kecamatan','kecamatan.id','=','master_desa.kecamatan_id')
            ->where('drainase.desa_id', $desa_id);
            // ->where('survey_drainase.ruas_drainase_id', $id_ruas_drainase)
            

            $total_panjang_ruas = DrainaseModel::select(DB::raw('SUM(drainase.panjang_ruas) as total_panjang_ruas'));
            if ($request->has('search') && $request->input('search')) {
                $valSearch = $request->input('search');
                $data->where(function ($query) use ($valSearch) {
                    $query->where('drainase.nama_ruas', 'like', '%' . $valSearch . '%')
                    ->orWhere('master_desa.nama', 'like', '%' . $valSearch . '%')
                    ->orWhere('kecamatan.name', 'like', '%' . $valSearch . '%');
                });

            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $data->whereRaw('MONTH(survey_drainase.created_at) = ?', [$searchMonth]);
            }
            
            if ($request->has('desa_id') && $request->input('desa_id')) {
                $searchTerm = $request->input('desa_id');
                $data->where(function ($query) use ($searchTerm) {
                    $query->where('drainase.desa_id',  $searchTerm);
                });
            }       
            
            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            $data = $data->paginate($paginate_count);
            $total_panjang_ruas = $total_panjang_ruas->first();

            $total_panjang_drainase = SurveyDrainaseModel::selectRaw('SUM(survey_drainase.panjang_drainase) as total_panjang_drainase')
                ->leftjoin('drainase', 'drainase.id', '=', 'survey_drainase.ruas_drainase_id')
                ->leftjoin('master_desa', 'master_desa.id', '=', 'drainase.desa_id')
                ->leftjoin('kecamatan', 'kecamatan.id', '=', 'master_desa.kecamatan_id')
                ->where('drainase.desa_id', $desa_id)
                // ->where('survey_drainase.ruas_drainase_id', $id_ruas_drainase)
                ->first();
            $data_total_panjang_ruas = '';
            $data_total_panjang_drainase = '';
            $data_total_panjang_drainase_kondisi_tanah ='';
            if ($total_panjang_drainase) {
                if ($total_panjang_drainase->total_panjang_drainase) {
                    $total_panjang = $total_panjang_drainase->total_panjang_drainase;
                    $total_panjang_drainase = $total_panjang;
                    $data_total_panjang_ruas = $total_panjang_ruas->total_panjang_ruas;
                    $data_total_panjang_drainase = $total_panjang_drainase;
                    $data_total_panjang_drainase_kondisi_tanah = (int) $total_panjang_ruas->total_panjang_ruas - (int) $total_panjang_drainase;
                }
            }


            return response()->json([
                'success' => true,
                'data' => $data,
                'data_total' => [
                            'total_panjang_ruas' => $data_total_panjang_ruas, 
                            'total_panjang_drainase' => $data_total_panjang_drainase, 
                            'total_panjang_drainase_kondisi_tanah' => $data_total_panjang_drainase_kondisi_tanah
                        ],
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
            $collection = SurveyDrainaseModel::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = SurveyDrainaseModel::find($id);
            $data->panjang_drainase = $request->panjang_drainase;
            $data->letak_drainase = $request->letak_drainase;
            $data->lebar_atas = $request->lebar_atas;
            $data->lebar_bawah = $request->lebar_bawah;
            $data->tinggi = $request->tinggi;
            $data->kondisi = $request->kondisi;
            $data->latitude = $request->latitude;
            $data->longitude = $request->longitude;
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
            $collection = SurveyDrainaseModel::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = SurveyDrainaseModel::find($id);
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

    public function upload_bukti_survey(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'desa_id' => 'required',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                 return response()->json(['error' => $validator->errors()->first()], 500);
            }

            $data = new SurveyDrainasePhoto();
            $data->desa_id = $request->desa_id;

            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $imageName = $image->getClientOriginalName();
                Storage::putFileAs('public/survey_drainase', $image, 'des-'.$request->desa_id.$imageName);
                $imageName = str_replace(" ","", 'des-'.$request->desa_id.$imageName);
                $data->photo = '/survey_drainase/'.strtolower($imageName);
            }
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

    public function export_drainase(Request $request)
    {
        try {
            $tahun = $request->tahun;
            $desa_id = $request->desa_id;
            // $id_ruas_drainase = $request->id_ruas_drainase;
            $excelFileName = 'survey-drainase' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';

            $excel = Excel::download(new SurveyDrainaseExport($tahun, $desa_id), $excelFileName);
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

    public function detail_survey(Request $request, $id)
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
            ->leftjoin('kecamatan','kecamatan.id','=','master_desa.kecamatan_id')
            ->find($id);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil menampilkan data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function statistic_drainase(Request $request)
    {
        try {
            $data = SurveyDrainaseModel::select(
                'survey_drainase.kondisi',
                DB::raw('COUNT(*) as count')
            );
            
            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $data->whereYear('survey_drainase.created_at', $tahun);
            }
            
            $data = $data->groupBy('survey_drainase.kondisi')->get();
            $kondisi_count = [];
            foreach ($data as $item) {
                $kondisi_count[$item->kondisi] = $item->count;
            }
            
            return response()->json([
                'success' => true,
                'data' => $kondisi_count,
                'message' => 'Berhasil menampilkan data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
