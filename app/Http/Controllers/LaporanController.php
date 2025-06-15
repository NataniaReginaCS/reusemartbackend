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
use App\Models\Request_donasi;
use App\Models\Organisasi;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class LaporanController extends Controller
{
    public function fetchAllPenitip(){
        try{
            $penitip = Penitip::all();
            if($penitip->isEmpty()){
                return response()->json(['message' => 'No penitip data found'], 404);
            }
            return response()->json([
                'message' => 'Berhasil mengambil data penitip',
                'data' => $penitip,
            ], 200);
        }catch(Exception $e){
            Log::error('Error fetchAllPenitip: '.$e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data penitip: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanTransaksiPenitip($id_penitip, $bulan, $tahun){
        try{
            Log::info('Memulai downloadLaporanTransaksiPenitip', [
            'id_penitip' => $id_penitip,
            'bulan' => $bulan,
            'tahun' => $tahun
        ]);
            $penitip = Penitip::find($id_penitip);
            if (!$penitip) {
            Log::warning('Penitip tidak ditemukan', ['id_penitip' => $id_penitip]);
            return response()->json(['error' => 'Penitip tidak ditemukan'], 404);
        }
            Log::info('Penitip data:', ['penitip' => $penitip ? $penitip->toArray() : null]);
            $komisi = DB::table('komisi')
            ->join('barang', 'komisi.id_barang', '=', 'barang.id_barang')
            ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
            ->join('detail_pembelian', 'komisi.id_barang', '=', 'detail_pembelian.id_barang')
            ->join('pembelian', 'detail_pembelian.id_pembelian', '=', 'pembelian.id_pembelian')
            ->where('komisi.id_penitip', $id_penitip)
            ->whereMonth('pembelian.tanggal_laku', $bulan)
            ->whereYear('pembelian.tanggal_laku', $tahun)
                ->select(
                    'barang.id_barang as id_barang',
                    'barang.nama as nama_barang',
                    'penitipan.tanggal_masuk as tanggal_masuk',
                    'pembelian.tanggal_lunas as tanggal_lunas',
                    'komisi.komisi_penitip as komisi_penitip',
                    'komisi.bonus_penitip as bonus_penitip',
                    
                )
                ->get();
                Log::info('Komisi data:', ['komisi' => $komisi->toArray()]);
                $totalHargaJual = $komisi->sum('komisi_penitip');
                $totalBonus = $komisi->sum('bonus_penitip');
                $totalPendapatan = $komisi->sum(function($item) {
                    return ($item->komisi_penitip ?? 0) + ($item->bonus_penitip ?? 0);
                });
                $data = $komisi->map(function ($item) {
                return [
                    'kode_barang' => $item->id_barang ?? "-",
                    'nama_barang' => $item->nama_barang ?? 'Produk Tidak Diketahui',
                    'tanggal_masuk' => $item->tanggal_masuk
                        ? date('d/m/Y', strtotime($item->tanggal_masuk))
                        : '-',
                    'tanggal_lunas' => $item->tanggal_lunas
                        ? date('d/m/Y', strtotime($item->tanggal_lunas))
                        : '-',
                    'komisi_penitip' => $item->komisi_penitip ?? 0,
                    'bonus_penitip' => $item->bonus_penitip ?? 0,
                    'pendapatan' => ($item->komisi_penitip ?? 0) + ($item->bonus_penitip ?? 0),
                ];
            })->toArray();
            Log::debug('Mapped data:', ['data' => $data]);
            $pdf = PDF::loadView('laporan.transaksi_penitip', [
                'data' => $data,
                'bulan' => $bulan ? date('F', mktime(0, 0, 0, $bulan, 1)) : '-',
                'tahun' =>$tahun ?? '-',
                'id_penitip' => $id_penitip,
                'nama_penitip' => $penitip ? $penitip->nama : '-',
                'tanggal_cetak' => now()->format('d F Y'),
                'totalHargaJual' => $totalHargaJual,
                'totalBonus' => $totalBonus,
                'totalPendapatan' => $totalPendapatan,
            ]);
            Log::info('PDF rendering selesai');

            Log::info('Data Laporan TransaksiPenitip:', ['data' => $data]);
            return $pdf->download('Laporan_Transaksi_Penitip_' . now()->format('Y-m-d') . '.pdf');
        }catch(Exception $e){
            Log::error('Error downloadTransaksiPenitip: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanRequestDonasi(){
        try{
            $organisasi = DB::table('organisasi')->join('request_donasi', 'organisasi.id_organisasi', '=', 'request_donasi.id_organisasi')
                ->select(
                    'organisasi.id_organisasi as id_organisasi',
                    'organisasi.nama as nama_organisasi',
                    'organisasi.alamat as alamat_organisasi',
                    'request_donasi.deskripsi as request'
                )->where('request_donasi.status_terpenuhi', '!=' , '0')->get();
                $data = $organisasi->map(function ($item) {
                return [
                    'id_organisasi' => $item->id_organisasi ?? '-',
                    'nama_organisasi' => $item->nama_organisasi ?? '-',
                    'alamat_organisasi' => $item->alamat_organisasi ?? '-',
                    'request' => $item->request ?? '-',
                ];
            })->toArray();
            $pdf = PDF::loadView('laporan.request_donasi', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
            ]);

            Log::info('Data Laporan Donasi Barang:', ['data' => $data]);
            return $pdf->download('Laporan_Donasi_Barang_' . now()->format('Y-m-d') . '.pdf');
        }catch(Exception $e){
            Log::error('Error downloadLaporanRequstDonasi: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }
    public function downloadLaporanDonasiBarang(){
        try{
            $barang = DB::table('barang')
                ->join('detail_donasi', 'barang.id_barang', '=', 'detail_donasi.id_barang')
                ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
                ->join('penitip', 'penitipan.id_penitip', '=', 'penitip.id_penitip')
                ->join('request_donasi', 'detail_donasi.id_request', '=', 'request_donasi.id_request')
                ->join('organisasi', 'request_donasi.id_organisasi', '=', 'organisasi.id_organisasi')
                ->where('barang.status_barang', 'didonasikan')
                ->select(
                    'barang.id_barang as id_barang',
                    'barang.nama as nama',
                    'penitip.id_penitip as id_penitip',
                    'penitip.nama as nama_penitip',
                    'detail_donasi.tanggal_donasi as tanggal_donasi',
                    'detail_donasi.nama_penerima as nama_penerima',
                    'organisasi.nama as nama_organisasi',
                )
                ->get();
            $data = $barang->map(function ($item) {
                return [
                    'kode_barang' => $item->id_barang ?? "-",
                    'nama_barang' => $item->nama ?? 'Produk Tidak Diketahui',
                    'id_penitip' => $item->id_penitip ?? '-',
                    'nama_penitip' => $item->nama_penitip ?? '-',
                    'tanggal_donasi' => $item->tanggal_donasi
                        ? date('d/m/Y', strtotime($item->tanggal_donasi))
                        : '-',
                    'nama_penerima' => $item->nama_penerima ?? '-',
                    'nama_organisasi' => $item->nama_organisasi ?? '-',
                ];
            })->toArray();
            $pdf = PDF::loadView('laporan.donasi_barang', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
            ]);

            Log::info('Data Laporan Donasi Barang:', ['data' => $data]);
            return $pdf->download('Laporan_Donasi_Barang_' . now()->format('Y-m-d') . '.pdf');
        }catch(Exception $e){
            Log::error('Error downloadLaporanDonasiBarang: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanDonasiBarangElektronik(){
        try{
            $barang = DB::table('barang')
                ->join('detail_donasi', 'barang.id_barang', '=', 'detail_donasi.id_barang')
                ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
                ->join('penitip', 'penitipan.id_penitip', '=', 'penitip.id_penitip')
                ->join('request_donasi', 'detail_donasi.id_request', '=', 'request_donasi.id_request')
                ->join('organisasi', 'request_donasi.id_organisasi', '=', 'organisasi.id_organisasi')
                ->join('kategori', 'barang.id_kategori', '=', 'kategori.id_kategori')
                ->where('kategori.id_kategori' ,'<', '10')
                ->where('barang.status_barang', 'didonasikan')
                ->select(
                    'barang.id_barang as id_barang',
                    'barang.nama as nama',
                    'penitip.id_penitip as id_penitip',
                    'penitip.nama as nama_penitip',
                    'detail_donasi.tanggal_donasi as tanggal_donasi',
                    'detail_donasi.nama_penerima as nama_penerima',
                    'organisasi.nama as nama_organisasi',
                    'kategori.nama as nama_kategori'
                )
                ->get();
            $data = $barang->map(function ($item) {
                return [
                    'kode_barang' => $item->id_barang ?? "-",
                    'nama_barang' => $item->nama ?? 'Produk Tidak Diketahui',
                    'id_penitip' => $item->id_penitip ?? '-',
                    'nama_penitip' => $item->nama_penitip ?? '-',
                    'tanggal_donasi' => $item->tanggal_donasi
                        ? date('d/m/Y', strtotime($item->tanggal_donasi))
                        : '-',
                    'nama_penerima' => $item->nama_penerima ?? '-',
                    'nama_organisasi' => $item->nama_organisasi ?? '-',
                    'nama_kategori' => $item->nama_kategori ?? '-',
                ];
            })->toArray();
            $pdf = PDF::loadView('laporan.donasi_barang_elektronik', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
            ]);

            Log::info('Data Laporan Donasi Barang:', ['data' => $data]);
            return $pdf->download('Laporan_Donasi_Barang_' . now()->format('Y-m-d') . '.pdf');
        }catch(Exception $e){
            Log::error('Error downloadLaporanDonasiBarang: '.$e->getMessage());
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }
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

    public function downloadLaporanKomisiBulanan(Request $request)
    {
        try {
            $bulan = (int) $request->input('month', now()->month);
            $tahun = (int) $request->input('year', now()->year);

            $komisi = Komisi::with(['komisiPenitip', 'komisiBarang', 'komisiPegawai'])
                ->whereHas('komisiBarang.detailpem.pembelian', function ($query) use ($bulan, $tahun) {
                    $query->whereMonth('tanggal_laku', $bulan)
                        ->whereYear('tanggal_laku', $tahun);
                })
                ->get();

            $idPenitipan = $komisi->map(function ($item) {
                return $item->komisiBarang->id_penitipan;
            })->filter()->unique()->values();


            $idBarang = $komisi->map(function ($item) {
                return $item->komisiBarang->id_barang;
            })->filter()->unique()->values();

            $penitipan = Penitipan::whereIn('id_penitipan', $idPenitipan)->get();
            $detailPembelian = Detail_pembelian::whereIn('id_barang', $idBarang)->get();

            $pembelian = Pembelian::whereIn('id_pembelian', $detailPembelian->pluck('id_pembelian'))
                ->whereMonth('tanggal_laku', $bulan)
                ->whereYear('tanggal_laku', $tahun)
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
                    'tanggal_masuk' => $penitipanItem && $penitipanItem->tanggal_masuk
                        ? date('d/m/Y', strtotime($penitipanItem->tanggal_masuk))
                        : '-',
                    'tanggal_laku' => $pembelianItem && $pembelianItem->tanggal_laku
                        ? date('d/m/Y', strtotime($pembelianItem->tanggal_laku))
                        : '-',
                    'komisi_hunter' => $item->komisi_hunter ?? 0,
                    'komisi_reusemart' => $item->komisi_reusemart ?? 0,
                    'bonus_penitip' => $item->bonus_penitip ?? 0,
                ];
            })->toArray();

            $totalKomisiHunter = 0;
            $totalKomisiReusemart = 0;
            $totalBonusPenitip = 0;
            $totalHargaJual = 0;
            foreach ($data as $key => $item) {
                $totalKomisiHunter += $item['komisi_hunter'];
                $totalKomisiReusemart += $item['komisi_reusemart'];
                $totalBonusPenitip += $item['bonus_penitip'];
                $totalHargaJual += $item['harga_jual'];
            }

            $pdf = PDF::loadView('laporan.komisi_bulanan', [
                'data' => $data,
                'tanggal_cetak' => now()->format('d F Y'),
                'bulan' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F'),
                'tahun' => $tahun,
                'totalKomisiHunter' => $totalKomisiHunter ?? 0,
                'totalKomisiReusemart' => $totalKomisiReusemart ?? 0,
                'totalBonusPenitip' => $totalBonusPenitip ?? 0,
                'totalHargaJual' => $totalHargaJual ?? 0,
            ]);

            return $pdf->download('Laporan_Komisi_Bulanan_' . $tahun . '_' . $bulan . '.pdf');
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menghasilkan laporan: ' . $e->getMessage()], 500);
        }
    }

    public function downloadLaporanPenjualanBulanan()
    {
        $barangTerjual = Barang::where('status_barang', 'sold out')
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

        $jumlahKotor = array_sum($totalPenjualanPerBulan);

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
            'jumlahKotor' => $jumlahKotor,
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


    public function fetchDataLaporanBarangHabis()
    {
        try {
            $data = DB::table('barang')
                ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
                ->join('penitip', 'penitipan.id_penitip', '=', 'penitip.id_penitip')
                ->where('barang.tanggal_akhir', '<', Carbon::today())
                ->select('barang.*', 'penitip.id_penitip', 'penitip.nama as nama_penitip')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve komisi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function fetchDataLaporanPenjualanKategori()
    {
        try {
            $currentYear = Carbon::now()->year;

            $data = DB::table('kategori')
                ->leftJoin('barang', 'kategori.id_kategori', '=', 'barang.id_kategori')
                ->leftJoin('detail_pembelian', 'barang.id_barang', '=', 'detail_pembelian.id_barang')
                ->leftJoin('pembelian', 'detail_pembelian.id_pembelian', '=', 'pembelian.id_pembelian')
                ->where('barang.status_barang', '!=', 'tersedia')
                ->select(
                    'kategori.id_kategori',
                    'kategori.nama',
                    DB::raw("SUM(CASE 
                            WHEN barang.status_barang IN ('terjual', 'didonasikan') 
                                 AND YEAR(pembelian.tanggal_laku) = $currentYear 
                            THEN 1 ELSE 0 END) as jumlah_terjual"),
                    DB::raw("SUM(CASE 
                            WHEN (barang.status_barang NOT IN ('terjual', 'didonasikan') 
                                AND YEAR(barang.tanggal_ambil) = $currentYear 
                                )
                            THEN 1 ELSE 0 END) as jumlah_gagal"),

                )
                ->groupBy('kategori.id_kategori', 'kategori.nama')
                ->orderBy('kategori.id_kategori')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

