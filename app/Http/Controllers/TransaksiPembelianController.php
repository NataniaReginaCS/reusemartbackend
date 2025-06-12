<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\Pembeli;
use App\Models\Penitip;
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

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TransaksiPembelianController extends Controller
{

    public function checkout(Request $request)
    {
        try {

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
                $barang = Barang::find($item->id_barang);
                if (!$barang) {
                    return response()->json(['message' => 'Barang tidak ditemukan'], 404);
                }
                $barang->status_barang = 'sold out';
                $barang->save();
                
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Checkout gagal',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    public function getOnGoingPembelian($nomor_nota)
    {
        $pembeli = auth('pembeli')->user();
        $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->where('id_pembeli', $pembeli->id_pembeli)->first();
        if (!$pembelian) {
            return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
        }
        return response()->json([
            'pembelian' => $pembelian,
        ]);
    }

    public function addBuktiPembayaran(Request $request, $nomor_nota)
    {
        $pembeli = auth('pembeli')->user();
        $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->where('id_pembeli', $pembeli->id_pembeli)->first();
        if (!$pembelian) {
            return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
        }

        $detailPembelian = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
        foreach ($detailPembelian as $item) {
            $barang = Barang::find($item->id_barang);
            if ($barang) {
                $barang->status_barang = 'terjual';
                $barang->save();
            }
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

    public function getUnverifiedPayment()
    {
        try {
            $pembelian = DB::table('pembelian')
                ->join('pembeli', 'pembelian.id_pembeli', '=', 'pembeli.id_pembeli')
                ->select('pembelian.*', 'pembeli.nama as nama_pembeli')
                ->where('pembelian.status_pembayaran', 'menunggu verifikasi')
                ->get();

            if ($pembelian->isEmpty()) {
                return response()->json(['message' => 'Tidak ada pembelian yang belum diverifikasi'], 404);
            }

            $pembelian = $pembelian->map(function ($item) {
                // Assuming bukti_pembayaran stores the filename or a path like "images/bukti_pembayaran/filename.png"
                $item->bukti_pembayaran = $item->bukti_pembayaran
                    ? asset('storage/images/bukti_pembayaran/' . basename($item->bukti_pembayaran))
                    : null;
                return $item;
            });

            return response()->json([
                'message' => 'Berhasil mendapatkan pembelian',
                'pembelian' => $pembelian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mendapatkan pembelian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPayment($nomor_nota)
    {
        try {
            $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->first();
            if (!$pembelian) {
                return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
            }

            $barang = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
            foreach ($barang as $item) {
                $itemBarang = Barang::with('barangPenitipan.penitipanPenitip')->where('id_barang', $item->id_barang)->first();
                if ($itemBarang) {
                    $this->sendNotification($itemBarang, 'Pembayaran Diterima', "Pembayaran untuk barang {$itemBarang->nama} yang anda titipkan telah diterima. Terima kasih telah menggunakan layanan kami.");
                }
            }

            $pembelian->status_pembayaran = 'lunas';
            $pembelian->status_pengiriman = 'disiapkan';
            $pembelian->save();

            return response()->json(['message' => 'Pembayaran berhasil diverifikasi'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memverifikasi pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function declinePayment($nomor_nota)
    {
        try {
            $pembelian = Pembelian::where('nomor_nota', $nomor_nota)->first();
            if (!$pembelian) {
                return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
            }


            $pembeli = Pembeli::find($pembelian->id_pembeli);
            $pembeli->poin += $pembelian->poin_digunakan;
            $pembeli->save();

            $detailPembelian = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
            foreach ($detailPembelian as $item) {
                $barang = Barang::find($item->id_barang);
                if ($barang) {
                    $barang->status_barang = 'tersedia';
                    $barang->save();
                }
            }

            if ($pembelian->bukti_pembayaran) {
                Storage::disk('public')->delete($pembelian->bukti_pembayaran);
            }

            $pembelian->status_pembayaran = 'batal';
            $pembelian->save();

            return response()->json(['message' => 'Pembayaran berhasil ditolak'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak pembayaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendNotification($barang, $title, $body)
    {
        $user = $barang->barangPenitipan?->penitipanPenitip;

        if ($user && $user->fcm_token) {
            $keyFile = config('firebase.projects.app.credentials.file');

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials($scopes, $keyFile);

            $token = $credentials->fetchAuthToken()['access_token'];

            $projectId = 'reusemart-a150d';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $user->fcm_token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                        ],
                    ]);


        }
    }
}
