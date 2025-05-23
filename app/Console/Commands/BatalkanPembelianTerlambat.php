<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use Carbon\Carbon;

class BatalkanPembelianTerlambat extends Command
{
    protected $signature = 'pembelian:batalkan-expired';
    protected $description = 'Membatalkan pembelian yang tidak dibayar setelah 15 menit';

    public function handle()
    {
        $batasWaktu = Carbon::now()->subMinutes(15);

        $pembelians = Pembelian::where('status_pembayaran', 'menunggu pembayaran')
            ->where('tanggal_laku', '<=', $batasWaktu)
            ->get();

        foreach ($pembelians as $pembelian) {
            $pembelian->status_pembayaran = 'batal';
            $pembeli = Pembeli::where('id_pembeli', $pembelian->id_pembeli)->first();
            if ($pembeli) {
                $pembeli->poin += $pembelian->poin_digunakan;
                $pembeli->save();
            }
            $pembelian->save();

        }

        $this->info('Pembelian kedaluwarsa berhasil dibatalkan.');
    }
}
