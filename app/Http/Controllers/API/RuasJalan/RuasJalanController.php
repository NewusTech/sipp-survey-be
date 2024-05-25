<?php

namespace App\Http\Controllers\API\RuasJalan;

use App\Http\Controllers\API\Survey\JenisPerkerasanController;
use App\Http\Controllers\Controller;
use App\Models\RuasJalan;
use App\Models\RuasJalanPhotos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RuasJalanController extends Controller
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
            $query = RuasJalan::select(
                        'master_ruas_jalan.id',
                        'master_ruas_jalan.no_ruas',
                        'master_ruas_jalan.nama',
                        'master_ruas_jalan.koridor_id',
                        'master_koridor.name as name_koridor',
                        'master_ruas_jalan.kecamatan as kecamatan_id',
                        'master_ruas_jalan.kabupaten',
                        'master_ruas_jalan.panjang_ruas',
                        'master_ruas_jalan.lebar',
                        'kecamatan.name as kecamatan',
                        'master_ruas_jalan.created_at'
                    )
                    ->leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
                    ->leftjoin('kecamatan','kecamatan.id','=', 'master_ruas_jalan.kecamatan')
                    ->latest();

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->whereYear('master_ruas_jalan.created_at', $tahun);
            }

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            $data = $query->paginate($paginate_count);
            $resData = $data->getCollection()->map(function ($query) {
                return [
                    "id" => $query->id,
                    "no_ruas" => $query->no_ruas,
                    "nama" => $query->nama,
                    "koridor_id" => $query->koridor_id,
                    "name_koridor" => $query->name_koridor,
                    "kecamatan_id" => $query->kecamatan_id,
                    "kabupaten" => $query->kabupaten,
                    "panjang_ruas" => $this->number_format($query->panjang_ruas),
                    "lebar" => $this->number_format($query->lebar),
                    "kecamatan" => $query->kecamatan,
                    "created_at" => $query->created_at
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
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'no_ruas' => 'required|unique:master_ruas_jalan',
                'nama' => 'required|unique:master_ruas_jalan',
                'panjang_ruas' => 'required'
            ]);

            if ($validator->fails()) {
                 return response()->json(['error' => $validator->errors()->first()], 500);
            }
            $ruas = new RuasJalan();
            $ruas->no_ruas      = $request->no_ruas;
            $ruas->nama         = $request->nama;
            $ruas->koridor_id   = $request->koridor_id;
            $ruas->panjang_ruas = $request->panjang_ruas;
            $ruas->akses        = $request->akses;
            $ruas->provinsi     = $request->provinsi;
            $ruas->kabupaten    = $request->kabupaten;
            $ruas->kecamatan    = $request->kecamatan_id;
            $ruas->desa         = $request->desa;
            $ruas->latitude     = $request->latitude;
            $ruas->longitude    = $request->longitude;
            $ruas->created_by   = Auth::id();
            $ruas->lebar        = $request->lebar;
            $ruas->save();

            $uploadedImages = [];

            if ($request->hasFile('images')) {
                $images = $request->file('images');
    
                foreach ($images as $image) {
                    $imageName = $image->getClientOriginalName();
                    Storage::putFileAs('public/ruas_jalan', $image, $imageName);
                    $uploadedImages[] = $imageName;
                    $imageName = str_replace(" ","", $imageName);
                    RuasJalanPhotos::create(['ruas_jalan_id' => $ruas->id, 'image' => '/ruas_jalan/'.strtolower($imageName)]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $ruas,
                'message' => 'Berhasil create data'
            ]); 
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $ruas = RuasJalan::leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
            ->leftjoin('ruas_jalan_photos','ruas_jalan_photos.ruas_jalan_id','=','master_ruas_jalan.id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->select(
                'master_ruas_jalan.id',
                'master_ruas_jalan.no_ruas',
                'master_koridor.id as koridor_id',
                'master_koridor.name as koridor_name',
                'master_ruas_jalan.nama as nama_ruas',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.lebar',
                'master_ruas_jalan.akses',
                'master_ruas_jalan.provinsi',
                'master_ruas_jalan.kabupaten',
                'master_ruas_jalan.kecamatan as kecamatan_id',
                'kecamatan.name as kecamatan',
                'master_ruas_jalan.desa',
                'master_ruas_jalan.latitude',
                'master_ruas_jalan.longitude',
                'ruas_jalan_photos.image'
            )->find($id);

            if (!$ruas) {
                return response()->json(['error' => 'id tidak ditemukan'], 500);
            }

            $selectPhoto = RuasJalanPhotos::where('ruas_jalan_id', $ruas->id)->get();
            $collectionImage = [];
            foreach ($selectPhoto as $key => $item) {
                $collectionImage[] = $item->image;
            }
            
            $ruas['image'] = $collectionImage;

            return response()->json([
                'success' => true,
                'data' => $ruas,
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
           $validator = Validator::make($request->all(), [
                'nama' => 'required',
                'panjang_ruas' => 'required'
            ]);

            if ($validator->fails()) {
                 return response()->json(['error' => $validator->errors()->first()], 500);
            }
            $update = RuasJalan::find($id);
            if (!$update) {
                return response()->json(['error' => 'id tidak ditemukan'], 500);
            }
            $update->nama         = $request->nama;
            $update->koridor_id   = $request->koridor_id;
            $update->panjang_ruas = $request->panjang_ruas;
            $update->akses        = $request->akses;
            $update->provinsi     = $request->provinsi;
            $update->kabupaten    = $request->kabupaten;
            $update->kecamatan    = $request->kecamatan_id;
            $update->desa         = $request->desa;
            $update->latitude     = $request->latitude;
            $update->longitude    = $request->longitude;
            $update->created_by   = Auth::id();
            $update->lebar        = $request->lebar;
            $update->update();

            $uploadedImages = [];

            if ($request->hasFile('images')) {
                $images = $request->file('images');
    
                RuasJalanPhotos::where('ruas_jalan_id', $update->id)->delete();
                foreach ($images as $image) {
                    $imageName = $image->getClientOriginalName();
                    Storage::putFileAs('public/ruas_jalan', $image, $imageName);
                    $uploadedImages[] = $imageName;
                    $imageName = str_replace(" ","", $imageName);
                    RuasJalanPhotos::create(['ruas_jalan_id' => $update->id, 'image' => '/ruas_jalan/'.strtolower($imageName)]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $update,
                'message' => 'Berhasil update data'
            ]); 
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $where = ['id' => $id];
            $collection = RuasJalan::where($where)->first();
            if (!$collection) {
                return response()->json([
                    'success' => false,
                    'data' => '',
                    'message' => 'ID tidak ditemukan'
                ]);
            }
            $data = RuasJalan::find($id);
            $data->delete();
            RuasJalanPhotos::where('ruas_jalan_id', $id)->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil delete data'
            ]); 
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getall()
    {
        try {
            $data = RuasJalan::select(
                        'master_ruas_jalan.id',
                        'master_ruas_jalan.no_ruas',
                        'master_ruas_jalan.nama',
                        'master_ruas_jalan.kabupaten'
                    )->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]); 
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRuasJalanById(Request $request, $id)
    {
        try {
            $data = RuasJalan::select(
                'master_ruas_jalan.id',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama',
                'master_ruas_jalan.panjang_ruas',
                'master_ruas_jalan.kabupaten',
                'master_koridor.name as name_koridor'
            )->leftjoin('master_koridor','master_koridor.id','=','master_ruas_jalan.koridor_id')
            ->where('master_ruas_jalan.id', $id)
            ->get();

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
