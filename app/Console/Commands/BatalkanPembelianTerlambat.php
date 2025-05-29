<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Pembeli;
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
