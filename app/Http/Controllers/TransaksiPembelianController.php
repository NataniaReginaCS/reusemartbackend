<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\Pembeli;
use App\Models\Keranjang;
use App\Models\Detail_pembelian;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class TransaksiPembelianController extends Controller
{

    public function checkout(Request $request){
        try{

            $IdPembeli = auth('pembeli')->user()->id_pembeli;

            $pembelian = Pembelian::where('id_pembeli', $IdPembeli)->where('status_pembayaran', 'menunggu pembayaran')->first();
            if ($pembelian) {
                return response()->json(['message' => 'Anda sudah melakukan pembelian, selesaikan pembelian sebelumnya terlebih dahulu'], 400);
            }
            
            $keranjang = Keranjang::where('id_pembeli', $IdPembeli)->get(); 
            if ($keranjang->isEmpty()) {
                return response()->json(['message' => 'Keranjang kosong.'], 400);
            }
            
            $totalBarang = 0;
            foreach ($keranjang as $item) {
                $totalBarang += $item->barang->harga;
            }
            
            $poinDigunakan = $request->poin_digunakan ?? 0;
            $diskonPoin = $poinDigunakan * 100;
            
            // Hitung poin didapat
            $poinDasar = floor($totalBarang / 10000);
            $poinBonus = $totalBarang > 500000 ? floor($poinDasar * 0.2) : 0;
            $poinDidapat = $poinDasar + $poinBonus;
            
            // Hitung ongkir otomatis
            $ongkir = 0;
            if ($request->metode_pengiriman === 'diantar') {
                $ongkir = $totalBarang > 1500000 ? 0 : 100000;
            }
            
            // Hitung total akhir
            $totalAkhir = $totalBarang + $ongkir - $diskonPoin;
            
            // Generate nomor nota (format: tahun.bulan.urutan)
            $count = Pembelian::count() + 1;
            $now = Carbon::now();
            $nomorNota = $now->format('y') . '.' . $now->format('m') . '.' . $count;
            
            // Kurangi poin dari user jika cukup
            $pembeli = Pembeli::find($IdPembeli);
            if ($pembeli->poin < $poinDigunakan) {
                return response()->json(['message' => 'Poin tidak cukup'], 400);
            }
            $pembeli->poin -= $poinDigunakan;
            $pembeli->save();
            
            // Buat pembelian baru
            $pembelian = Pembelian::create([
                'id_pembeli' => $IdPembeli,
                'id_pegawai' => null,
                'id_alamat' => $request->id_alamat, // akan diset nanti
                'tanggal_laku' => Carbon::now(),
                'status_pembayaran' => 'menunggu pembayaran',
                'status_pengiriman' => $request->status_pengiriman, // ambil_sendiri / diantar
                'metode_pengiriman' => $request->metode_pengiriman,
                
                'ongkir' => $ongkir,
                'poin_digunakan' => $poinDigunakan,
                'poin_didapat' => $poinDidapat,
                'total' => $totalAkhir,
                'nomor_nota' => $nomorNota,
            ]);
            
            
            foreach ($keranjang as $item) {
                $detailPembelian = Detail_pembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $item->id_barang,
                ]);
                $item->delete();
                
            }
            
            return response()->json([
                'message' => 'Checkout berhasil',
                'pembelian' => $pembelian,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Checkout gagal',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    public function getOnGoingPembelian($nomor_nota){
        $pembeli = auth('pembeli')->user();
        $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->where('id_pembeli', $pembeli->id_pembeli )->first();
        if (!$pembelian) {
            return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
        }
        return response()->json([
            'pembelian' => $pembelian,
        ]);
    }
    
    public function addBuktiPembayaran(Request $request , $nomor_nota){
        $pembeli = auth('pembeli')->user();
        $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->where('id_pembeli', $pembeli->id_pembeli )->first();
        if (!$pembelian) {
            return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
        }
        
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $filePath = $file->store('images/bukti_pembayaran', 'public');
            $pembelian->bukti_pembayaran = $filePath;
            $pembelian->status_pembayaran = 'menunggu verifikasi';
            $pembelian->tanggal_lunas = Carbon::now();
            $pembelian->save();

            return response()->json([
                'message' => 'Bukti pembayaran berhasil diunggah',
                'bukti_pembayaran' => $pembelian->bukti_pembayaran,
            ]);
        } else {
            return response()->json(['message' => 'File tidak ditemukan'], 400);
        }
    }

    public function getUnverifiedPayment(){
        try{
            $pembelian = DB::table('pembelian')
                ->join('pembeli', 'pembelian.id_pembeli', '=', 'pembeli.id_pembeli')
                ->select('pembelian.*', 'pembeli.nama as nama_pembeli')
                ->where('pembelian.status_pembayaran', 'menunggu verifikasi')
                ->get();
            
            if ($pembelian->isEmpty()) {
                return response()->json(['message' => 'Tidak ada pembelian yang belum diverifikasi'], 404);
            }
            $pembelian->asset($pembelian->bukti_pembayaran);
            return response()->json([
                'pembelian' => $pembelian,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Gagal mendapatkan pembelian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
