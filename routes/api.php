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
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\KeranjangController;

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
use App\Http\Controllers\DiskusiController;
use App\Http\Controllers\Detail_donasiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransaksiPembelianController;
use App\Http\Controllers\PenitipanController;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\Penukaran_poinController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/cekRole', [AuthController::class, 'cekRole']);
Route::middleware('auth:sanctum')->post('/send-notification', [NotificationController::class, 'send']);
Route::middleware('auth:sanctum')->post('/update-fcm-token', [NotificationController::class, 'updateFcmToken']);
Route::post('/send-welcome-notification', [NotificationController::class, 'sendWelcomeNotification'])->middleware('auth:sanctum');

//Link Email 
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

//Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/loginMobile', [AuthController::class, 'loginMobile']);


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
Route::get('/fetchDiskusi/{idBarang}', [DiskusiController::class, 'fetchDiskusi']);
Route::get('/fetchRoles', [RoleController::class, 'fetchRoles']);
Route::get('/getPenitip/{id}', [BarangController::class, 'getPenitip']);

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
    
    //Keranjang 
    Route::post('/addToKeranjang/{id}', [KeranjangController::class, 'addToKeranjang']);
    Route::get('/fetchKeranjang', [KeranjangController::class, 'fetchKeranjang']);
    Route::delete('/deleteKeranjang/{id}', [KeranjangController::class, 'deleteKeranjang']);
    Route::post('/checkout', [TransaksiPembelianController::class, 'checkout']);


    //Checkout
    Route::get('/getOngoingPembelian/{nomor_nota}', [TransaksiPembelianController::class, 'getOngoingPembelian']);
    Route::post('/addBuktiPembayaran/{nomor_nota}', [TransaksiPembelianController::class, 'addBuktiPembayaran']);
    
    
    Route::post('/createRating', [RatingController::class, 'createRating']);
    Route::get('/getRating/{id_barang}', [RatingController::class, 'getRating']);
    Route::get('/fetchRating', [RatingController::class, 'fetchRating']);
});

Route::middleware(['auth:sanctum', PenitipMiddleware::class])->group(function () {
    Route::get('/fetchPenitipByLogin', [PenitipController::class, 'fetchPenitipByLogin']);
    Route::get('/fetchHistoryTransaksi', [PenitipController::class, 'fetchHistoryTransaksi']);
    Route::get('/fetchHistoryTransaksiById/{id}', [PenitipController::class, 'fetchHistoryTransaksiById']);
    Route::get('/fetchBarangbyPenitip', [PenitipController::class, 'fetchBarangbyPenitip']);
    Route::get('/fetchBarangPenitipById/{id}', [PenitipController::class, 'fetchBarangPenitipById']);
    Route::post('/showExtendProduct/{id}', [PenitipController::class, 'showExtendProducts   ']);
    Route::post('/extendBarangPenitip', [PenitipController::class, 'extendBarangPenitip']);
    Route::post('/ambilBarangPenitip', [PenitipController::class, 'ambilBarangPenitip']);
    Route::post('/save-token', [PenitipController::class, 'saveFcmToken']);
});

Route::middleware(['auth:sanctum', OrganisasiMiddleware::class])->group(function () {
    Route::prefix('request_donasi')->group(function () {
        Route::get('/', [Request_donasiController::class, 'index']);
        Route::get('/show', [Request_donasiController::class, 'show']);
        Route::post('/', [Request_donasiController::class, 'store']);
        Route::put('/{id}', [Request_donasiController::class, 'update']);
        Route::put('/{id}/alokasi', [Request_donasiController::class, 'alokasi']);
        Route::delete('/{id}', [Request_donasiController::class, 'destroy']);
        
        Route::get('/search', [Request_donasiController::class, 'search']);
        Route::get('/filterByDate', [Request_donasiController::class, 'filterByDate']);
        Route::get('/filterByStatus', [Request_donasiController::class, 'filterByStatus']);
        Route::get('/organisasi/{id_organisasi}/history', [Request_donasiController::class, 'historyByOrganisasi']);
    });
});

Route::middleware(['auth:sanctum', CSMiddleware::class])->group(function () {
    Route::post('/addPenitip', [PenitipController::class, 'addPenitip']);
    Route::get('/fetchPenitip', [PenitipController::class, 'fetchPenitip']);
    Route::post('/updatePenitip/{id}', [PenitipController::class, 'updatePenitip']);
    Route::delete('/deletePenitip/{id}', [PenitipController::class, 'deletePenitip']);
    Route::get('/fetchDiskusiCS', [DiskusiController::class, 'fetchDiskusiCS']);

    //Verifikasi
    Route::get('/getUnverifiedPayment', [TransaksiPembelianController::class, 'getUnverifiedPayment']);
    Route::post('/verifyPayment/{nomor_nota}', [TransaksiPembelianController::class, 'verifyPayment']);
    Route::post('/declinePayment/{nomor_nota}', [TransaksiPembelianController::class, 'declinePayment']);

    //Merchandise
    Route::get('/getPenukaranPoin', [Penukaran_poinController::class, 'getPenukaranPoin']);
    Route::post('/updatePenukaranPoin/{id}', [Penukaran_poinController::class, 'updatePenukaranPoin']);
});

Route::middleware('auth:sanctum')->get('/order-history', [PembelianController::class, 'getOrderHistory']);
Route::middleware('auth:sanctum')->get('/order-history/{id}', [PembelianController::class, 'getOrderHistoryById']);
Route::middleware('auth:sanctum')->get('/order-details/{id}', [PembelianController::class, 'getOrderDetails']);

Route::middleware('auth:sanctum')->get('/order-history', [PembelianController::class, 'getOrderHistory']);
Route::middleware('auth:sanctum')->get('/order-history/{id}', [PembelianController::class, 'getOrderHistoryById']);
Route::middleware('auth:sanctum')->get('/order-details/{id}', [PembelianController::class, 'getOrderDetails']);

Route::post('/addDiskusi/{id}', [DiskusiController::class, 'addDiskusi']);

Route::middleware(['auth:sanctum', GudangMiddleware::class])->group(function () {

    Route::get('/fetchDiskusiCS', [DiskusiController::class, 'fetchDiskusiCS']);
    Route::get('/fetchTransaksiByGudang', [PegawaiController::class, 'fetchTransaksiByGudang']);
    Route::get('/fetchTransaksiGudangById/{id}', [PegawaiController::class, 'fetchTransaksiGudangById']);
    Route::put('/updateTanggalPengiriman/{id}', [PegawaiController::class, 'update']);
    Route::get('/fetchDataPembelian/{id}', [PegawaiController::class, 'fetchDataPembelian']);
    Route::get('/fetchDataPegawai', [PegawaiController::class, 'fetchDataPegawai']);

});

Route::post('/addDiskusi/{id}', [DiskusiController::class, 'addDiskusi']);

//Gudang
Route::middleware(['auth:sanctum', GudangMiddleware::class])->group(function () {
    Route::get('/fetchPenitipan', [PenitipanController::class, 'index']);
    Route::post('/addPenitipan', [PenitipanController::class, 'store']);
    Route::post('/updatePenitipan/{id}', [PenitipanController::class, 'update']);
    Route::get('/showPenitipan/{id}', [PenitipanController::class, 'show']);
    Route::get('/showPenitipPenitipan/{id}', [PenitipanController::class, 'showPenitip']);
    Route::get('/showPegawaiPenitipan/{id}', [PenitipanController::class, 'showPegawai']);
    Route::get('/showBarangPenitipan/{id}', [PenitipanController::class, 'showBarang']);
    Route::get('/showAllBarang', [PenitipanController::class, 'showAllBarang']);
    Route::get('/fetchPenitipPenitipan', [PenitipanController::class, 'fetchPenitipPenitipan']);
    Route::get('/fetchPegawaiPenitipan', [PenitipanController::class, 'fetchPegawaiPenitipan']);
    Route::post('/updateOnlyPenitipan/{id}', [PenitipanController::class, 'updatePenitipan']);
    Route::post('/storeBarang/{id}', [PenitipanController::class, 'storeBarang']);
});

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {

    Route::get('/fetchOrganisasi', [OrganisasiController::class, 'fetchOrganisasi']);

    Route::get('/fetchPegawai', [PegawaiController::class, 'index']);
    Route::post('/updatePegawai/{id}', [PegawaiController::class, 'updatePegawai']);
    Route::delete('/deletePegawai/{id}', [PegawaiController::class, 'deletePegawai']);
    Route::post('/addPegawai', [PegawaiController::class, 'addPegawai']);
    Route::get('/searchPegawai', [PegawaiController::class, 'searchPegawai']);

    Route::get('/fetchOrganisasi', [OrganisasiController::class, 'fetchOrganisasi']);

    Route::post('/updateOrganisasi/{id}', [OrganisasiController::class, 'updateOrganisasi']);
    Route::delete('/deleteOrganisasi/{id}', [OrganisasiController::class, 'deleteOrganisasi']);

    Route::post('/resetPassword/{id}', [PegawaiController::class, 'resetPasswordPegawai']);


    Route::post('/resetPassword/{id}', [PegawaiController::class, 'resetPasswordPegawai']);

});

Route::middleware(['auth:sanctum', OwnerMiddleware::class])->group(function () {
    // Mendonasikan Barang
    Route::get('/fetchRequest', [Detail_donasiController::class, 'fetchRequest']);
    Route::get('/showOrganisasi', [Detail_donasiController::class, 'showOrganisasi']);
    Route::post('/store', [Detail_donasiController::class, 'store']);
    Route::post('/updateDonasi/{id}', [Detail_donasiController::class, 'updateDonasi']);
    Route::get('/historyDonasibyOrganisasi/{id}', [Detail_donasiController::class, 'historyDonasibyOrganisasi']);
    Route::get('/fetchDetailDonasi', [Detail_donasiController::class, 'fetchDetailDonasi']);
    Route::get('/fetchBarangForDonasi', [Detail_donasiController::class, 'fetchBarangForDonasi']);
    Route::get('/fetchAllBarang', [Detail_donasiController::class, 'fetchAllBarang']);

});



