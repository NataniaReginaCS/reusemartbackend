<?php
namespace App\Console\Commands;

use App\Models\Penitip;
use App\Models\Barang;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Google\Auth\Credentials\ServiceAccountCredentials;


class CheckPenitipanExpiry extends Command
{
    protected $signature = 'penitipan:check-expiry';
    protected $description = 'Check penitipan expiry and send notifications';

    protected $messaging;

    public function __construct(Messaging $messaging)
    {
        parent::__construct();
        $this->messaging = $messaging;
    }

    // public function handle()
    // {
    //     $today = Carbon::today();
    //     $threeDaysFromNow = $today->copy()->addDays(3);

    //     $barangList = Barang::where('status_barang', 'tersedia')
    //     ->with(['barangPenitipan.penitipanPenitip' => function ($query) {
    //         $query->whereNotNull('fcm_token');
    //     }])
    //     ->get();

    //     if ($barangList->isEmpty()) {
    //         $this->info('No barang found for expiry check.');
    //         return;
    //     }

    //     foreach ($barangList as $barang) {
    //         if (!$barang->barangPenitipan || !$barang->barangPenitipan->penitipanPenitip) {
    //             $this->info("Skipping: No active penitipan or penitip with FCM token found for barang {$barang->id_barang}");
    //             continue;
    //         }
    //         $tanggalAkhir = Carbon::parse($barang->tanggal_akhir);
    //         $penitip = $barang->barangPenitipan->penitipanPenitip;

    //         if ($tanggalAkhir->isSameDay($threeDaysFromNow)) {
    //             $this->sendNotification(
    //                 $penitip->fcm_token,
    //                 'Peringatan H-3',
    //                 "Masa penitipan barang {$barang->nama} akan berakhir pada {$tanggalAkhir->format('d/m/Y')}."
    //             );
    //         }

    //         if ($tanggalAkhir->isSameDay($today)) {
    //             $this->sendNotification(
    //                 $penitip->fcm_token,
    //                 'Peringatan Hari H',
    //                 "Masa penitipan barang {$barang->nama} berakhir hari ini ({$tanggalAkhir->format('d/m/Y')})."
    //             );
    //         }
    //     }

    //     $this->info('Penitipan expiry check completed at ' . now()->toDateTimeString());
    // }


    // private function sendNotification($token, $title, $body)
    // {
    //     try {
    //         $notification = Notification::create($title, $body);
    //         $message = CloudMessage::withTarget('token', $token)
    //             ->withNotification($notification);

    //         $this->messaging->send($message);
    //         $this->info("Notification sent to token: {$token} - {$title}");
    //     } catch (\Exception $e) {
    //         $this->error("Failed to send notification to token: {$token}. Error: {$e->getMessage()}");
    //     }
    // }

    public function handle()
    {
        $today = Carbon::today('Asia/Jakarta');
        $barangs = Barang::whereNotNull('tanggal_akhir')
        ->where('status_barang', 'tersedia')
        ->get();

        foreach ($barangs as $barang) {
            $endDate = Carbon::parse($barang->tanggal_akhir, 'Asia/Jakarta');
            $daysDifference = $today->diffInDays($endDate, false);

            if ($daysDifference == 3) {
                $this->sendNotification($barang, "Peringatan H-3", "Masa penitipan barang {$barang->nama} akan berakhir pada {$endDate->format('d/m/Y')}.");
            } elseif ($daysDifference == 0) {
                $this->sendNotification($barang, "Peringatan Hari H", "Masa penitipan barang {$barang->nama} berakhir hari ini ({$endDate->format('d/m/Y')}).");
            }
        }

        $this->info("Penitipan expiry check completed at " . now()->toDateTimeString());
    }

    private function sendNotification($barang, $title, $body)
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
        } else {
            $this->warn("No active penitipan or user with FCM token found for barang ID: {$barang->id_barang}");
        }
    }
}