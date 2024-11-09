<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Dashboard\DashboardController;
use App\Http\Controllers\API\Desa\DesaController;
use App\Http\Controllers\API\Drainase\DrainaseController;
use App\Http\Controllers\API\Drainase\SurveyDrainaseController;
use App\Http\Controllers\API\Export\ExportController;
use App\Http\Controllers\API\Import\ImportController;
use App\Http\Controllers\API\Jembatan\JembatanController;
use App\Http\Controllers\API\Kecamatan\KecamatanController;
use App\Http\Controllers\API\Laporan\LaporanController;
use App\Http\Controllers\API\MasterKoridor\MasterKoridorController;
use App\Http\Controllers\API\RuasJalan\RuasJalanController;
use App\Http\Controllers\API\Survey\JenisPerkerasanController;
use App\Http\Controllers\API\Survey\KondisiPerkerasanController;
use App\Http\Controllers\API\User\UserController;
// use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifikatorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/refresh', [AuthController::class, 'refresh'])->name('refresh');
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'koridor'
], function () {
    Route::resource('master_koridor', MasterKoridorController::class);
    Route::get('list', [MasterKoridorController::class, 'getall']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'ruas_jalan'
], function () {
    Route::resource('master_ruas_jalan', RuasJalanController::class);
    Route::get('list', [RuasJalanController::class, 'getall']);
    Route::get('listbyid/{id}', [RuasJalanController::class, 'getRuasJalanById']);
    Route::post('update/{id}', [RuasJalanController::class, 'update']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'user'
], function () {
    Route::post('update/{id}', [UserController::class, 'updateProfile']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'survey'
], function () {
    Route::resource('jenis_perkerasan', JenisPerkerasanController::class);
    Route::resource('kondisi_perkerasan', KondisiPerkerasanController::class);

    Route::post('export_byrow', [JenisPerkerasanController::class, 'exportByRow']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'laporan'
], function () {
    // periodik
    Route::get('jenis_perkerasan', [JenisPerkerasanController::class, 'LaporanIndex']);
    Route::get('kondisi_perkerasan', [KondisiPerkerasanController::class, 'LaporanList']);
    Route::get('tingkat_kemantapan', [LaporanController::class, 'kemantapan']);
    Route::get('show_kemantapan/{id}', [LaporanController::class, 'show_kemantapan']);
    Route::post('export_kemantapan', [LaporanController::class, 'export_kemantapan']);
    // end periodik

    Route::get('statistik/jenis_perkerasan', [LaporanController::class, 'jenis_perkerasan']);
    Route::get('statistik/kondisi_perkerasan', [LaporanController::class, 'kondisi_perkerasan']);
    Route::get('statistik/rigit', [LaporanController::class, 'getrigit']);
    Route::get('statistik/hotmix', [LaporanController::class, 'gethotmix']);
    Route::get('statistik/lapen', [LaporanController::class, 'getlapen']);
    Route::get('statistik/telford', [LaporanController::class, 'gettelford']);
    Route::get('statistik/tanah', [LaporanController::class, 'gettanah']);
    Route::get('statistik/baik', [LaporanController::class, 'getbaik']);
    Route::get('statistik/sedang', [LaporanController::class, 'getsedang']);
    Route::get('statistik/rusak_ringan', [LaporanController::class, 'getrusak_ringan']);
    Route::get('statistik/rusak_berat', [LaporanController::class, 'getrusak_berat']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'dashboard'
], function () {
    Route::get('', [DashboardController::class, 'index']);
    Route::get('lokasi_jalan', [DashboardController::class, 'lokasi_jalan']);
    Route::get('barchart', [DashboardController::class, 'barchart']);
    Route::get('kemantapan', [DashboardController::class, 'kemantapan']);
    Route::get('maps', [DashboardController::class, 'maps']); //ruas jalan
    Route::get('maps_jembatan', [DashboardController::class, 'maps_jembatan']); //jembatan
    Route::get('piechart', [DashboardController::class, 'piechart']);
    Route::get('detail_mantap', [DashboardController::class, 'detail_mantap']);
    Route::get('detail_tmantap', [DashboardController::class, 'detail_tmantap']);
    Route::get('drainase', [DashboardController::class, 'drainase']);
    Route::get('maps_drainase', [DashboardController::class, 'maps_drainase']);
});

Route::group([
    'middleware' => 'api'
], function () {
    Route::resource('jembatan', JembatanController::class);
    Route::post('jembatan/update/{id}', [JembatanController::class, 'update']);
    Route::post('jembatan/export_byrow', [JembatanController::class, 'exportByRow']);
    Route::get('statistic_jembatan', [JembatanController::class, 'statistic_jembatan']);
    Route::get('detail_statistic_jembatan', [JembatanController::class, 'detail_statistic_jembatan']);
});

Route::group([
    'middleware' => 'api'
], function () {
    Route::resource('kecamatan', KecamatanController::class);
});

Route::group([
    'middleware' => 'api'
], function () {
    Route::get('export_survey_jalan', [ExportController::class, 'export_excel']);
    Route::get('export_survey_jembatan', [ExportController::class, 'export_jembatan']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'import'
], function () {
    Route::post('ruas_jalan', [ImportController::class, 'import_ruas_jalan']);
    Route::post('jembatan', [ImportController::class, 'import_jembatan']);
});

Route::group([
    'middleware' => 'api',
], function () {
    Route::get('/template/{filename}', [ImportController::class, 'download_template']);
});

Route::group([
    'middleware' => 'api',
], function () {
    Route::resource('drainase', DrainaseController::class);
});

Route::group([
    'middleware' => 'api',
], function () {
    Route::resource('master_desa', DesaController::class);
});

Route::group([
    'middleware' => 'api',
], function () {
    Route::resource('survey_drainase', SurveyDrainaseController::class);
    Route::get('survey_drainase/detail', [SurveyDrainaseController::class, 'show']);
    Route::post('survey_drainase/uplaod', [SurveyDrainaseController::class, 'upload_bukti_survey']);
    Route::get('export_drainase', [SurveyDrainaseController::class, 'export_drainase']);
    Route::get('detail_survey_drainase/{id}', [SurveyDrainaseController::class, 'detail_survey']);
    Route::get('statistic_drainase', [SurveyDrainaseController::class, 'statistic_drainase']);
    Route::get('detail_statistic_drainase', [SurveyDrainaseController::class, 'detail_statistic_drainase']);
});

// Verifikasi
Route::group([
    'middleware' => ['api', VerifikatorMiddleware::class],
], function () {
    Route::put('survey_drainase/verify/{id}', [SurveyDrainaseController::class, 'verify']);
    Route::put('ruas_jalan/{id}/verify', [RuasJalanController::class, 'verify']);
    Route::put('jembatan/verify/{id}', [JembatanController::class, 'verify']);
});
