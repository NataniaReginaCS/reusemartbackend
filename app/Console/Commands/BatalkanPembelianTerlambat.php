<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Pembeli;
use App\Models\Detail_pembelian;
use App\Models\Barang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BatalkanPembelianTerlambat extends Command
{
    protected $signature = 'pembelian:batalkan-expired';
    protected $description = 'Membatalkan pembelian yang tidak dibayar setelah 1 menit';

    public function handle()
    {
        $batasWaktu = Carbon::now()->subMinutes(1);

        $pembelians = Pembelian::where('status_pembayaran', 'menunggu pembayaran')
            ->where('tanggal_laku', '<=', $batasWaktu)
            ->get();

        foreach ($pembelians as $pembelian) {
            $pembelian->status_pembayaran = 'batal';
            $pembeli = Pembeli::where('id_pembeli', $pembelian->id_pembeli)->first();
            $detail_barang = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
            foreach ($detail_barang as $detail) {
                $barang = Barang::where('id_barang', $detail->id_barang)->first();
                $barang->status_barang = 'tersedia';
                $barang->save();
            }
        
            Log::info('Pembelian dibatalkan: ' . $pembelian->id_pembelian);
            if ($pembeli) {
                $pembeli->poin += $pembelian->poin_digunakan ?? 0;
                $pembeli->save();
            }
            $pembelian->save();

        }

        $this->info('Pembelian kedaluwarsa berhasil dibatalkan.');
    }
}
