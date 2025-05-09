<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Request_donasiController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenitipController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\AlamatController;
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Middleware\PenitipMiddleware;
use App\Http\Middleware\PembeliMiddleware;
use App\Http\Middleware\OrganisasiMiddleware;
use App\Http\Middleware\CSMiddleware;
use App\Http\Middleware\GudangMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\KurirMiddleware;
use App\Http\Middleware\HunterMiddleware;
use App\Http\Controllers\KategoriController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/cekRole', [AuthController::class, 'cekRole']);

//Link Email 
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/registerPembeli', [AuthController::class, 'registerPembeli']);
Route::post('/registerOrganisasi', [AuthController::class, 'registerOrganisasi']);
Route::get('/fetchKategori', [KategoriController::class, 'fetchKategori']); 
Route::get('/showBarangbyKategori/{id_kategori}', [BarangController::class, 'showBarangbyKategori']);
Route::get('/relatedProducts/{id_kategori}', [BarangController::class, 'relatedProducts']);
Route::get('/fetchBarang', [BarangController::class, 'index']);
Route::get('/showBarang/{id}', [BarangController::class, 'show']);
Route::post('/searchBarang', [BarangController::class, 'searchBarang']);
Route::get('/showBarangIsGaransi', [BarangController::class, 'showBarangIsGaransi']);
Route::get('/showBarangIsNotGaransi', [BarangController::class, 'showBarangIsNotGaransi']);
Route::get('/showNamaPenitip/{id}', [BarangController::class, 'showNamaPenitip']);



Route::middleware(['auth:sanctum', PembeliMiddleware::class])->group(function () {
    Route::get('/fetchAlamat', [AlamatController::class, 'fetchAlamat']);
    Route::get('/fetchPembeli', [PembeliController::class, 'fetchPembeli']);
    Route::get('/alamatUtama', [PembeliController::class, 'findUtama']);
    Route::post('/tambahAlamat', [PembeliController::class, 'addAlamat']);
    Route::post('/findAlamat', [PembeliController::class, 'findUtama']);
    Route::post('/addAlamat', [AlamatController::class, 'addAlamat']);
    Route::post('/editAlamat/{id}', [AlamatController::class, 'updateAlamat']);
    Route::delete('/deleteAlamat/{id}', [AlamatController::class, 'deleteAlamat']);
    Route::post('/setUtama/{id}', [AlamatController::class, 'setUtama']);
});

Route::middleware(['auth:sanctum', PenitipMiddleware::class])->group(function () {

});

Route::middleware(['auth:sanctum', OrganisasiMiddleware::class])->group(function () {

});

Route::middleware(['auth:sanctum', CSMiddleware::class])->group(function () {

});

Route::middleware(['auth:sanctum', GudangMiddleware::class])->group(function () {

});

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {

});

Route::get('/fetchOrganisasi', [OrganisasiController::class, 'fetchOrganisasi']);
Route::post('/updateOrganisasi/{id}', [OrganisasiController::class, 'updateOrganisasi']);
Route::delete('/deleteOrganisasi/{id}', [OrganisasiController::class, 'deleteOrganisasi']);
Route::post('/addPenitip', [PenitipController::class, 'addPenitip']);
Route::get('/fetchPenitip', [PenitipController::class, 'fetchPenitip']);
Route::post('/updatePenitip/{id}', [PenitipController::class, 'updatePenitip']);
Route::delete('/deletePenitip/{id}', [PenitipController::class, 'deletePenitip']);


Route::middleware(['auth:sanctum', OwnerMiddleware::class])->group(function () {

});

Route::prefix('request_donasi')->group(function () {
    Route::get('/', [Request_donasiController::class, 'index']);
    Route::get('/{id}', [Request_donasiController::class, 'show']);
    Route::post('/', [Request_donasiController::class, 'store']);
    Route::put('/{id}', [Request_donasiController::class, 'update']);
    Route::put('/{id}/alokasi', [Request_donasiController::class, 'alokasi']);
    Route::delete('/{id}', [Request_donasiController::class, 'destroy']);

    Route::get('/search', [Request_donasiController::class, 'search']);
    Route::get('/filterByDate', [Request_donasiController::class, 'filterByDate']);
    Route::get('/filterByStatus', [Request_donasiController::class, 'filterByStatus']);
    Route::get('/organisasi/{id_organisasi}/history', [Request_donasiController::class, 'historyByOrganisasi']);
});


Route::prefix('barang')->group(function () {
    Route::get('/', [BarangController::class, 'index']);
    Route::get('/available', [BarangController::class, 'available']);
    Route::get('/{id}', [BarangController::class, 'show']);
    Route::get('/garansi/{id}', [BarangController::class, 'checkGaransi']);
    Route::post('/', [BarangController::class, 'store']);
    Route::put('/{id}', [BarangController::class, 'update']);
    Route::delete('/{id}', [BarangController::class, 'destroy']);
    Route::get('/search', [BarangController::class, 'search']);
});

Route::prefix('pegawai')->group(function () {
    Route::post('/register', [PegawaiController::class, 'register']);
    Route::get('/', [PegawaiController::class, 'index']);
    Route::get('/{id}', [PegawaiController::class, 'show']);
    Route::put('/{id}', [PegawaiController::class, 'update']);
    Route::delete('/{id}', [PegawaiController::class, 'destroy']);
    Route::get('/search', [PegawaiController::class, 'search']);
});


