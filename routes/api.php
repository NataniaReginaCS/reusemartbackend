<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Request_donasiController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PembeliController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/registerPembeli', [AuthController::class, 'registerPembeli']);
Route::post('/tambahAlamat', [PembeliController::class,'addAlamat']);
Route::post('/findAlamat', [PembeliController::class,'findUtama']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);


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


