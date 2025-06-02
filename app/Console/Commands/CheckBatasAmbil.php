<?php

namespace App\Console\Commands;
use DB;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Pembelian;


class CheckBatasAmbil extends Command
{
    protected $signature = 'check:batas-ambil';
    protected $description = 'Check if the batas ambil for each pembelian has passed and update the status accordingly';
    public function handle()
    {
        $now = Carbon::now();
        $twoDaysAgo = $now->copy()->subDays(2);

        $pembelians = DB::table('pembelian')
            ->join('detail_pembelian', 'pembelian.id_pembelian', '=', 'detail_pembelian.id_pembelian')
            ->join('barang', 'detail_pembelian.id_barang', '=', 'barang.id_barang')
            ->where('pembelian.metode_pengiriman', '=', 'Diambil')
            ->where('barang.batas_ambil', '<=', $twoDaysAgo)
            ->select('pembelian.id_pembelian', 'barang.id_barang') // Only get needed columns
            ->get();

        foreach ($pembelians as $pembelian) {
            DB::table('pembelian')
                ->where('id_pembelian', $pembelian->id_pembelian)
                ->update(['status_pengiriman' => 'Hangus']);

            DB::table('barang')
                ->where('id_barang', $pembelian->id_barang)
                ->update(['status_barang' => 'Didonasikan']);
        }
    }

}