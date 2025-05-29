<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Penitip;
use App\Models\Penitipan;
use App\Models\Pegawai;
use App\Models\Detail_penitipan;
use App\Models\Komisi;
use App\Models\Detail_pembelian;
use App\Models\Pembelian;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class LaporanController extends Controller
{
    public function downloadLaporanStokGudang()
    {
        try {
            $barang = Barang::with(['barangPenitipan.penitipanPenitip', 'barangPenitipan.penitipanPegawai'])
                ->where('status_barang', 'tersedia')
                ->get();

            if ($barang->isEmpty()) {
                return response()->json(['message' => 'No penitipan data found'], 404);
            }

            $data = $barang->map(function ($item) {
                return [
                    'kode_barang' => $item->id_barang ?? "-",
                    'nama_barang' => $item->nama ?? 'Produk Tidak Diketahui',
                    'id_penitip' => $item->barangPenitipan->penitipanPenitip->id_penitip ?? '-',
                    'nama_penitip' => $item->barangPenitipan->penitipanPenitip->nama ?? '-',
                    'tanggal_masuk' => $item->barangPenitipan->tanggal_masuk
                        ? date('d/m/Y', strtotime($item->barangPenitipan->tanggal_masuk))
                        : '-',
                    'perpanjangan' => $item->barangPenitipan->status_perpanjangan ?? 'Tidak',
                    'id_hunter' => $item->id_hunter,
                    'nama_hunter' => Pegawai::find($item->id_hunter)->nama ?? '-',
                    'harga' => $item->harga ?? 0,
                ];
            })->toArray();

            $pdf = PDF::loadView('laporan.stok_gudang', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
            ]);
            return $pdf->download('Laporan_Stok_Gudang_' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanKomisiBulanan()
    {
        try {
            $komisi = Komisi::with(['komisiPenitip', 'komisiBarang', 'komisiPegawai'])
                ->whereHas('komisiBarang.detailpem.pembelian', function ($query) {
                    $query->whereMonth('tanggal_laku', now()->month)
                        ->whereYear('tanggal_laku', now()->year);
                })
                ->get();
            \Log::info('Data Komisi:', ['komisi' => $komisi]);
            $idPenitipan = $komisi->map(function ($item) {
                return $item->komisiBarang->id_penitipan;
            })->filter()->unique()->values();
    
            $idBarang = $komisi->map(function ($item) {
                return $item->komisiBarang->id_barang;
            })->filter()->unique()->values();
    
            $penitipan = Penitipan::whereIn('id_penitipan', $idPenitipan)->get();
            $detailPembelian = Detail_pembelian::whereIn('id_barang', $idBarang)->get();
    
            $pembelian = Pembelian::whereIn('id_pembelian', $detailPembelian->pluck('id_pembelian'))
                ->whereMonth('tanggal_laku', now()->month)
                ->whereYear('tanggal_laku', now()->year)
                ->get();
    
            $data = $komisi->map(function ($item) use ($penitipan, $detailPembelian, $pembelian) {
                $barang = optional($item->komisiBarang);
                $detail = $detailPembelian->where('id_barang', $barang->id_barang)->first();
                $pembelianItem = $detail ? $pembelian->where('id_pembelian', $detail->id_pembelian)->first() : null;
                $penitipanItem = $penitipan->where('id_penitipan', $barang->id_penitipan)->first();
    
                return [
                    'kode_produk' => $barang->id_barang ?? '-',
                    'nama_produk' => $barang->nama ?? 'Produk Tidak Diketahui',
                    'harga_jual' => $barang->harga ?? 0,
                    'tanggal_masuk' => $penitipanItem->tanggal_masuk
                    ? date('d/m/Y', strtotime( $penitipanItem->tanggal_masuk))
                    : '-',
                    'tanggal_laku' => $pembelianItem->tanggal_laku
                    ? date('d/m/Y', strtotime($pembelianItem->tanggal_laku))
                    : '-',
                    'komisi_hunter' => $item->komisi_hunter ?? 0,
                    'komisi_reusemart' => $item->komisi_reusemart ?? 0,
                    'bonus_penitip' => $item->bonus_penitip ?? 0,
                ];
            })->toArray();

            $pdf = PDF::loadView('laporan.komisi_bulanan', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
                'bulan' => now()->format('F'),
                'tahun' => now()->year,
            ]);

            return $pdf->download('Laporan_Komisi_Bulanan_' . now()->format('Y-m-d') . '.pdf');
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanPenjualanBulanan()
    {
        $barangTerjual = Barang::where('status_barang', 'terjual')
            ->with('detailpem.pembelian')
            ->get();

        $totalBarangTerjualPerBulan = [];
        $totalPenjualanPerBulan = [];
        $year = now()->year;

        foreach (range(1, 12) as $bulan) {
            $totalBarangTerjualPerBulan[$bulan] = $barangTerjual->filter(function ($barang) use ($bulan, $year) {
            $tanggalLaku = null;
            if ($barang->detailpem && $barang->detailpem->count() > 0) {
                foreach ($barang->detailpem as $detail) {
                if ($detail->pembelian && $detail->pembelian->tanggal_laku) {
                    $bulanLaku = (int)date('m', strtotime($detail->pembelian->tanggal_laku));
                    $tahunLaku = (int)date('Y', strtotime($detail->pembelian->tanggal_laku));
                    if ($bulanLaku === $bulan && $tahunLaku === $year) {
                    $tanggalLaku = $detail->pembelian->tanggal_laku;
                    break;
                    }
                }
                }
            }
            return $tanggalLaku && (int)date('m', strtotime($tanggalLaku)) === $bulan && (int)date('Y', strtotime($tanggalLaku)) === $year;
            })->count();

            $totalPenjualanPerBulan[$bulan] = $barangTerjual->filter(function ($barang) use ($bulan, $year) {
            $tanggalLaku = null;
            $harga = 0;
            if ($barang->detailpem && $barang->detailpem->count() > 0) {
                foreach ($barang->detailpem as $detail) {
                if ($detail->pembelian && $detail->pembelian->tanggal_laku) {
                    $bulanLaku = (int)date('m', strtotime($detail->pembelian->tanggal_laku));
                    $tahunLaku = (int)date('Y', strtotime($detail->pembelian->tanggal_laku));
                    if ($bulanLaku === $bulan && $tahunLaku === $year) {
                    $tanggalLaku = $detail->pembelian->tanggal_laku;
                    break;
                    }
                }
                }
            }
            return $tanggalLaku && (int)date('m', strtotime($tanggalLaku)) === $bulan && (int)date('Y', strtotime($tanggalLaku)) === $year;
            })->sum('harga');
        }

        $penjualanData = [
            ['month' => 'Januari', 'sales' => $totalPenjualanPerBulan[1] ?? 0, 'items' => $totalBarangTerjualPerBulan[1] ?? 0],
            ['month' => 'Februari', 'sales' => $totalPenjualanPerBulan[2] ?? 0, 'items' => $totalBarangTerjualPerBulan[2] ?? 0],
            ['month' => 'Maret', 'sales' => $totalPenjualanPerBulan[3] ?? 0, 'items' => $totalBarangTerjualPerBulan[3] ?? 0],
            ['month' => 'April', 'sales' => $totalPenjualanPerBulan[4] ?? 0, 'items' => $totalBarangTerjualPerBulan[4] ?? 0],
            ['month' => 'Mei', 'sales' => $totalPenjualanPerBulan[5] ?? 0, 'items' => $totalBarangTerjualPerBulan[5] ?? 0],
            ['month' => 'Juni', 'sales' => $totalPenjualanPerBulan[6] ?? 0, 'items' => $totalBarangTerjualPerBulan[6] ?? 0],
            ['month' => 'Juli', 'sales' => $totalPenjualanPerBulan[7] ?? 0, 'items' => $totalBarangTerjualPerBulan[7] ?? 0],
            ['month' => 'Agustus', 'sales' => $totalPenjualanPerBulan[8] ?? 0, 'items' => $totalBarangTerjualPerBulan[8] ?? 0],
            ['month' => 'September', 'sales' => $totalPenjualanPerBulan[9] ?? 0, 'items' => $totalBarangTerjualPerBulan[9] ?? 0],
            ['month' => 'Oktober', 'sales' => $totalPenjualanPerBulan[10] ?? 0, 'items' => $totalBarangTerjualPerBulan[10] ?? 0],
            ['month' => 'November', 'sales' => $totalPenjualanPerBulan[11] ?? 0, 'items' => $totalBarangTerjualPerBulan[11] ?? 0],
            ['month' => 'Desember', 'sales' => $totalPenjualanPerBulan[12] ?? 0, 'items' => $totalBarangTerjualPerBulan[12] ?? 0],
        ];

        $chartHTML = $this->generateChartHTML($penjualanData);
        $chartImagePath = public_path('charts/sales-chart.png');

        try {
            Browsershot::html($chartHTML)
                ->setOption('args', ['--no-sandbox'])
                ->windowSize(800, 400)
                ->waitUntilNetworkIdle()
                ->save($chartImagePath);
        } catch (\Exception $e) {
            \Log::error('Browsershot failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghasilkan chart: ' . $e->getMessage());
        }
        
    
        $data = [
            'penjualanData' => $penjualanData,
            'chartImagePath' => $chartImagePath,
            'year' => now()->year,
            'tanggal_cetak' => now()->format('d F Y'),
        ];
        $pdf = Pdf::loadView('laporan.penjualan_bulanan', $data);
        return $pdf->download('Laporan_Penjualan_Bulanan_' . now()->format('Y-m-d') . '.pdf');
    }

    private function generateChartHTML($penjualanData)
    {
        $labels = json_encode(array_column($penjualanData, 'month'));
        $sales = json_encode(array_column($penjualanData, 'sales'));

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        </head>
        <body>
            <canvas id="salesChart" width="800" height="400"></canvas>
            <script>
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: $labels,
                        datasets: [{
                            label: 'Jumlah Penjualan Kotor',
                            data: $sales,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                min: 0,
                                max: 160000000,
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 20000000,
                                    callback: function(value) {
                                        return value;
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        },
                        barPercentage: 1.0,
                        categoryPercentage: 1.0
                    }
                });
            </script>
        </body>
        </html>
        HTML;

        return $html;
    }    
}

