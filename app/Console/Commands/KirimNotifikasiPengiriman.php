<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\Pembeli;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Detail_pembelian;
use App\Models\Barang;
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;


class KirimNotifikasiPengiriman extends Command
{
    protected $signature = 'pengiriman:kirim-notifikasi';
    protected $description = 'Mengirim notifikasi pengiriman untuk pembeli dan penitip ketika kurir mengantar';

    public function handle()
    {
        $today = Carbon::now();

        $pembelianList = Pembelian::where('status_pengiriman', 'disiapkan')->where('tanggal_pengiriman', $today->format('Y-m-d'))->where('metode_pengiriman', '=', 'diantar')->get();
        foreach($pembelianList as $pembelian){
            $barang = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
            $pembeli = Pembeli::where('id_pembeli', $pembelian->id_pembeli)->first();

            if ($pembeli) {
                $this->sendNotificationToPembeli($pembeli, 'Pengiriman Barang', "Transaksi dengan nomor nota {$pembelian->nomor_nota} akan dikirim hari ini. Terima kasih telah menggunakan layanan kami.");
            }
            foreach ($barang as $item) {
                $itemBarang = Barang::with('barangPenitipan.penitipanPenitip')->where('id_barang', $item->id_barang)->first();
                if ($itemBarang) {
                    $this->sendNotificationToPenitip($itemBarang, 'Pengiriman Barang', "Barang atas nama {$itemBarang->nama} yang anda titipkan akan dikirim hari ini. Terima kasih telah menggunakan layanan kami.");
                }
            }
        }
    
    }

    private function sendNotificationToPembeli($user, $title, $body)
    {
    
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

            if ($response->successful()) {
                $this->info("Notification sent to token: {$user->fcm_token} - {$title}");
            } else {
                $this->error("Failed to send notification: " . $response->body());
            }
        } 
    }

    private function sendNotificationToPenitip($barang, $title, $body)
    {
        $user = $barang->barangPenitipan->penitipanPenitip;

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

             if ($response->successful()) {
                $this->info("Notification sent to token: {$user->fcm_token} - {$title}");
            } else {
                $this->error("Failed to send notification: " . $response->body());
            }
        
        } 
    }
}
