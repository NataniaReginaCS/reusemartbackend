<?php
namespace App\Console\Commands;

use App\Models\Penitip;
use App\Models\Barang;
use Carbon\Carbon;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Console\Command;

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

    public function handle()
    {
        $today = Carbon::today();
        $threeDaysFromNow = $today->copy()->addDays(3);

        // Ambil semua barang yang belum diambil
        $barangList = Barang::where('status_barang', '!=', 'Diambil')->get();

        if ($barangList->isEmpty()) {
            $this->info('No barang found for expiry check.');
            return;
        }

        foreach ($barangList as $barang) {
            $tanggalAkhir = Carbon::parse($barang->tanggal_akhir);

            // Ambil data penitipan untuk barang tersebut yang belum diambil
            $penitipan = \App\Models\Penitipan::where('id_penitipan', $barang->id_penitipan)
                ->first();

            // Ambil penitip berdasarkan id_penitip dari penitipan (jika ada)
            if (!$penitipan || !$penitipan->id_penitip) {
                $this->info("Skipping: No active penitipan found for barang {$barang->id_barang}");
                continue;
            }

            $penitip = \App\Models\Penitip::find($penitipan->id_penitip);

            if (!$penitip || !$penitip->fcm_token) {
                $this->info("Skipping: No penitip or FCM token found for barang {$barang->id_barang}");
                continue;
            }

            if ($tanggalAkhir->isSameDay($threeDaysFromNow)) {
                $this->sendNotification(
                    $penitip->fcm_token,
                    'Peringatan H-3',
                    "Masa penitipan barang {$barang->nama} akan berakhir pada {$tanggalAkhir->format('d/m/Y')}."
                );
            }

            // Kirim notifikasi Hari H
            if ($tanggalAkhir->isSameDay($today)) {
                $this->sendNotification(
                    $penitip->fcm_token,
                    'Peringatan Hari H',
                    "Masa penitipan barang {$barang->nama} berakhir hari ini ({$tanggalAkhir->format('d/m/Y')})."
                );
            }
        }

        $this->info('Penitipan expiry check completed at ' . now()->toDateTimeString());
    }


    private function sendNotification($token, $title, $body)
    {
        try {
            $notification = Notification::create($title, $body);
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            $this->messaging->send($message);
            $this->info("Notification sent to token: {$token} - {$title}");
        } catch (\Exception $e) {
            $this->error("Failed to send notification to token: {$token}. Error: {$e->getMessage()}");
        }
    }
}