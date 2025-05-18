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
        $IdPembeli = auth()->guard('pembeli')->user()->id_pembeli;
        $keranjang = Keranjang::where('id_pembeli', $IdPembeli)->get(); 
        if ($keranjang->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 400);
        }
        
        $totalBarang = 0;
        foreach ($keranjang as $item) {
            $totalBarang += $item->barang->harga;
        }

        $poinDigunakan = $request->poin_digunakan ?? 0;
        $diskonPoin = $poinDigunakan * 10000;

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
            'tanggal_laku' => now(),
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
    }
}
