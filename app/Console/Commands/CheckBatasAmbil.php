<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Carbon\Carbon;

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
            ->select('pembelian.id_pembelian', 'barang.id_barang')
            ->get()
            ->groupBy('id_pembelian');

        foreach ($pembelians as $id_pembelian => $barangs) {
            DB::table('pembelian')->where('id_pembelian', $id_pembelian)->update([
                'status_pengiriman' => 'Hangus'
            ]);

            $idBarangList = $barangs->pluck('id_barang');

            DB::table('barang')->whereIn('id_barang', $idBarangList)->update([
                'status_barang' => 'Didonasikan'
            ]);

            $barangList = DB::table('detail_pembelian')
                ->join('barang', 'detail_pembelian.id_barang', '=', 'barang.id_barang')
                ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
                ->whereIn('barang.id_barang', $idBarangList)
                ->select(
                    'barang.id_barang',
                    'barang.nama',
                    'barang.harga',
                    'barang.id_hunter',
                    'barang.status_perpanjangan',
                    'penitipan.id_penitip',
                    'penitipan.tanggal_masuk'
                )
                ->get();

            $walletPerPenitip = [];
            $walletPerHunter = [];

            foreach ($barangList as $barang) {
                $harga = $barang->harga;
                $komisi_penitip = 0;
                $komisi_hunter = 0;
                $komisi_reusemart = 0;
                $bonus_penitip = 0;

                if ($barang->id_hunter) {
                    if ($barang->status_perpanjangan == 0) {
                        $komisi_penitip = $harga * 0.8;
                        $komisi_hunter = $harga * 0.05;
                        $komisi_reusemart = $harga * 0.15;
                    } else {
                        $komisi_penitip = $harga * 0.7;
                        $komisi_hunter = $harga * 0.05;
                        $komisi_reusemart = $harga * 0.25;
                    }
                } else {
                    if ($barang->status_perpanjangan == 0) {
                        $komisi_penitip = $harga * 0.8;
                        $komisi_reusemart = $harga * 0.2;
                    } else {
                        $komisi_penitip = $harga * 0.7;
                        $komisi_reusemart = $harga * 0.3;
                    }
                }

                if (Carbon::parse($barang->tanggal_masuk)->greaterThanOrEqualTo(Carbon::now()->subDays(7))) {
                    if ($barang->status_perpanjangan == 0) {

                        $bonus_penitip = $komisi_reusemart * 0.1;
                        $komisi_reusemart -= $bonus_penitip;
                    } else {
                        $bonus_penitip = 0;
                    }
                }

                DB::table('komisi')->insert([
                    'id_barang' => $barang->id_barang,
                    'id_penitip' => $barang->id_penitip,
                    'id_pegawai' => $barang->id_hunter,
                    'id_pembelian' => $id_pembelian,
                    'komisi_reusemart' => $komisi_reusemart,
                    'komisi_penitip' => $komisi_penitip,
                    'komisi_hunter' => $komisi_hunter,
                    'bonus_penitip' => $bonus_penitip,
                ]);

                $walletPerPenitip[$barang->id_penitip] = ($walletPerPenitip[$barang->id_penitip] ?? 0) + ($komisi_penitip + $bonus_penitip);

                if ($barang->id_hunter) {
                    $walletPerHunter[$barang->id_hunter] = ($walletPerHunter[$barang->id_hunter] ?? 0) + $komisi_hunter;
                }
            }

            foreach ($walletPerPenitip as $id_penitip => $amount) {
                DB::table('penitip')->where('id_penitip', $id_penitip)->increment('wallet', $amount);
            }

            foreach ($walletPerHunter as $id_hunter => $amount) {
                DB::table('pegawai')->where('id_pegawai', $id_hunter)->increment('wallet', $amount);
            }
        }
    }
}
