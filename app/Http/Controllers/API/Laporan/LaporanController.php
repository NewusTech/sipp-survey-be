<?php

namespace App\Http\Controllers\API\Laporan;

use App\Exports\KemantapanExport;
use App\Http\Controllers\Controller;
use App\Models\JenisPerkerasan;
use App\Models\KondisiPerkerasan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function kemantapan(Request $request)
    {
        try {
            $paginate_count = 10;

            $jenis_perkerasan = JenisPerkerasan::leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->leftjoin('master_koridor', 'master_koridor.id', '=', 'master_ruas_jalan.koridor_id')
                ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
                ->select(
                    'jenis_perkerasan.id',
                    'jenis_perkerasan.ruas_jalan_id',
                    'master_ruas_jalan.nama as nama_ruas',
                    'master_koridor.id as id_koridor',
                    'master_koridor.name as nama_koridor',
                    'master_ruas_jalan.panjang_ruas',
                    'jenis_perkerasan.baik',
                    'jenis_perkerasan.sedang',
                    'jenis_perkerasan.rusak_ringan',
                    'jenis_perkerasan.rusak_berat',
                    'jenis_perkerasan.created_at',
                    'kecamatan.name as name_kecamatan'
                )->latest();

            if ($request->has('paginate_count') && $request->input('paginate_count')) {
                $paginate_count = $request->input('paginate_count');
            }

            if ($request->has('search') && $request->input('search')) {
                $searchTerm = $request->input('search');
                $jenis_perkerasan->where(function ($query) use ($searchTerm) {
                    $query->where('master_ruas_jalan.nama', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('month') && $request->input('month')) {
                $searchMonth = $request->input('month');
                $jenis_perkerasan->whereRaw('MONTH(jenis_perkerasan.created_at) = ?', [$searchMonth]);
            }

            $data = $jenis_perkerasan->paginate($paginate_count);
            $resData = $data->getCollection()->map(function ($query) {
                $mantap = number_format((($query->baik + $query->sedang) / $query->panjang_ruas) * 100, 3);
                $tmantap = number_format((($query->rusak_ringan + $query->rusak_berat) / $query->panjang_ruas) * 100, 3);
                return [
                    "id"                => $query->id,
                    "ruas_jalan_id"     => $query->ruas_jalan_id,
                    "nama_ruas"         => $query->nama_ruas,
                    "id_koridor"        => $query->id_koridor,
                    "nama_koridor"      => $query->nama_koridor,
                    "panjang_ruas"      => $this->number_format($query->panjang_ruas),
                    "baik"              => $this->number_format($query->baik),
                    "sedang"            => $this->number_format($query->sedang),
                    "rusak_ringan"      => $this->number_format($query->rusak_ringan),
                    "rusak_berat"       => $this->number_format($query->rusak_berat),
                    "mantap"            => $mantap, // ((baik+sedang)/panjang_ruas) * 100)
                    "tmantap"           => $tmantap, //  ((rusak_ringan+rusak_berat) * 100)
                    "kecamatan"         => $query->name_kecamatan //  ((rusak_ringan+rusak_berat) * 100)
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

    public function export_kemantapan(Request $request)
    {
        try {
            $year = $request->year;
            $id_survey = $request->id_survey;
            $excelFileName = 'kemantapan-row-' . Str::random(9) . '-' . Carbon::now()->toDateString() . '.xlsx';
            if (!$id_survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'id_survey tidak boleh kosong.',
                    'data' => null
                ]);
            }
            $excel = Excel::download(new KemantapanExport($year, $id_survey), $excelFileName);
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

    public function show_kemantapan(Request $request, $id)
    {
        try {
            $data = JenisPerkerasan::leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->leftjoin('master_koridor', 'master_koridor.id', '=', 'master_ruas_jalan.koridor_id')
                ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
                ->select(
                    'jenis_perkerasan.ruas_jalan_id',
                    'master_ruas_jalan.nama as nama_ruas',
                    'master_koridor.id as id_koridor',
                    'master_koridor.name as nama_koridor',
                    'master_ruas_jalan.panjang_ruas',
                    'jenis_perkerasan.baik',
                    'jenis_perkerasan.sedang',
                    'jenis_perkerasan.rusak_ringan',
                    'jenis_perkerasan.rusak_berat',
                    'jenis_perkerasan.created_at',
                    'kecamatan.name as name_kecamatan'
                );
                
            $data = $data->find($id);
            if ($data) {
                $mantap = number_format((($data->baik + $data->sedang) / $data->panjang_ruas) * 100, 3);
                $tmantap = number_format((($data->rusak_ringan + $data->rusak_berat) / $data->panjang_ruas) * 100, 3);
            
                $formattedData = [
                    "id"                => $data->ruas_jalan_id,
                    "nama_ruas"         => $data->nama_ruas,
                    "id_koridor"        => $data->id_koridor,
                    "nama_koridor"      => $data->nama_koridor,
                    "panjang_ruas"      => $this->number_format($data->panjang_ruas),
                    "baik"              => $this->number_format($data->baik),
                    "sedang"            => $this->number_format($data->sedang),
                    "rusak_ringan"      => $this->number_format($data->rusak_ringan),
                    "rusak_berat"       => $this->number_format($data->rusak_berat),
                    "mantap"            => $this->number_format($mantap),
                    "tmantap"           => $this->number_format($tmantap),
                    "kecamatan"         => $data->name_kecamatan
                ];
            
                return response()->json([
                    'success' => true,
                    'data' => $formattedData,
                    'message' => 'Berhasil mendapatkan data'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

          
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function jenis_perkerasan(Request $request)
    {
        try {
            $jenis_perkerasan = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                DB::raw('SUM(jenis_perkerasan.hotmix) as hotmix_count'),
                DB::raw('SUM(jenis_perkerasan.rigit) as rigit_count'),
                DB::raw('SUM(jenis_perkerasan.lapen) as lapen_count'),
                DB::raw('SUM(jenis_perkerasan.telford) as telford_count'),
                DB::raw('SUM(jenis_perkerasan.tanah) as tanah_count'),
                'jenis_perkerasan.created_at'
            )->groupBy('jenis_perkerasan.id', 'jenis_perkerasan.created_at')
            ->latest();

            $jenis_perkerasan = $jenis_perkerasan->first();

            $hotmix_count = $jenis_perkerasan->hotmix_count;
            $rigit_count = $jenis_perkerasan->rigit_count;
            $lapen_count = $jenis_perkerasan->lapen_count;
            $telford_count = $jenis_perkerasan->telford_count;
            $tanah_count = $jenis_perkerasan->tanah_count;

            $data = $hotmix_count + $rigit_count + $lapen_count + $telford_count + $tanah_count;
            $jenis_perkerasan['total'] = $data;

            return response()->json([
                'success' => true,
                'data' => $jenis_perkerasan,
                'message' => 'Berhasil get data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function kondisi_perkerasan()
    {
        try {
            $jenis_perkerasan = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                DB::raw('SUM(jenis_perkerasan.baik) as baik_count'),
                DB::raw('SUM(jenis_perkerasan.sedang) as sedang_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_ringan) as rusak_ringan_count'),
                DB::raw('SUM(jenis_perkerasan.rusak_berat) as rusak_berat_count'),
                DB::raw('SUM(master_ruas_jalan.panjang_ruas) as panjang_ruas_count'),
                'jenis_perkerasan.created_at',
            )
                ->groupBy('jenis_perkerasan.id', 'jenis_perkerasan.created_at')
                ->leftjoin('master_ruas_jalan', 'master_ruas_jalan.id', '=', 'jenis_perkerasan.ruas_jalan_id')
                ->latest();

            $jenis_perkerasan = $jenis_perkerasan->get();
            $baik_count         = 0;
            $sedang_count       = 0;
            $rusak_ringan_count = 0;
            $rusak_berat_count  = 0;
            $panjang_ruas_count  = 0;
            foreach ($jenis_perkerasan as $key => $item) {
                $baik_count += $item->baik_count;
                $sedang_count += $item->sedang_count;
                $rusak_ringan_count += $item->rusak_ringan_count;
                $rusak_berat_count += $item->rusak_berat_count;
                $panjang_ruas_count  += $item->panjang_ruas_count;
            }
            $data = $baik_count + $sedang_count + $rusak_ringan_count + $rusak_berat_count;

            $baik_percentage = ($baik_count / $data) * 100;
            $sedang_percentage = ($sedang_count / $data) * 100;
            $rusak_ringan_percentage = ($rusak_ringan_count / $data) * 100;
            $rusak_berat_percentage = ($rusak_berat_count / $data) * 100;
            //kemantapan
            $mantap_percentage = ($baik_count + $sedang_count) / $panjang_ruas_count * 100;
            $tmantap_percentage = ($rusak_ringan_count + $rusak_berat_count) / $panjang_ruas_count * 100;

            $resp['baik_percentage'] = $baik_percentage ? round($baik_percentage, 3) : "";
            $resp['sedang_percentage'] = $sedang_percentage ? round($sedang_percentage, 3) : "";
            $resp['rusak_ringan_percentage'] = $rusak_ringan_percentage ? round($rusak_ringan_percentage, 3) : "";
            $resp['rusak_berat_percentage'] = $rusak_berat_percentage ? round($rusak_berat_percentage, 3) : "";
            $resp['total'] = $data ? round($data, 3) : "";

            $resp['mantap'] = round($mantap_percentage, 3);
            $resp['tmantap'] = round($tmantap_percentage, 3);

            return response()->json([
                'success' => true,
                'data' => $resp,
                'message' => 'Berhasil get data'
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getrigit(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.rigit',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.rigit')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $totalRigit = $data->sum('rigit');
            $data->transform(function ($item) {
                $item->rigit = $this->number_format($item->rigit);
                return $item;
            });
            $data['total'] = $totalRigit;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function gethotmix(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.hotmix',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.hotmix')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('hotmix');
            $data->transform(function ($item) {
                $item->hotmix = $this->number_format($item->hotmix);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function getlapen(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.lapen',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.lapen')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('lapen');
            $data->transform(function ($item) {
                $item->lapen = $this->number_format($item->lapen);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function gettelford(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.telford',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.telford')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('telford');
            $data->transform(function ($item) {
                $item->telford = $this->number_format($item->telford);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function gettanah(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.tanah',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.tanah')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('tanah');
            $data->transform(function ($item) {
                $item->tanah = $this->number_format($item->tanah);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function getbaik(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.baik',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.baik')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('baik');
            $data->transform(function ($item) {
                $item->baik = $this->number_format($item->baik);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function getsedang(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.sedang',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.sedang')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('sedang');
            $data->transform(function ($item) {
                $item->sedang = $this->number_format($item->sedang);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function getrusak_ringan(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.rusak_ringan',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.rusak_ringan')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('rusak_ringan');
            $data->transform(function ($item) {
                $item->rusak_ringan = $this->number_format($item->rusak_ringan);
                return $item;
            });
            $data['total'] = $total;
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Berhasil get data'
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function getrusak_berat(Request $request)
    {
        try {
            $query = JenisPerkerasan::select(
                'jenis_perkerasan.id',
                'jenis_perkerasan.rusak_berat',
                'master_ruas_jalan.no_ruas',
                'master_ruas_jalan.nama AS ruas_jalan',
                'kecamatan.name AS kecamatan',
                'jenis_perkerasan.created_at'
            )->leftjoin('master_ruas_jalan','master_ruas_jalan.id','=','jenis_perkerasan.ruas_jalan_id')
            ->leftjoin('kecamatan','kecamatan.id','=','master_ruas_jalan.kecamatan')
            ->whereNotNull('jenis_perkerasan.rusak_berat')
            ->latest();

            if ($request->has('year') && $request->input('year')) {
                $tahun = $request->input('year');
                $query->where('jenis_perkerasan.tahun', $tahun);
            }
            $data = $query->get();
            $total = $data->sum('rusak_berat');
            $data->transform(function ($item) {
                $item->rusak_berat = $this->number_format($item->rusak_berat);
                return $item;
            });
            $data['total'] = $total;
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
