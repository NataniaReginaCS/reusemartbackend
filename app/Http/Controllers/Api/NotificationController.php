<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penitip;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationController extends Controller
{
    public function send(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'body' => 'required',
                'id_penitip' => 'nullable',
            ]);
    
            $query = Penitip::query();
            if ($request->id_penitip) {
                $query->where('id_penitip', $request->id_penitip);
            }
            $penitips = $query->whereNotNull('fcm_token')->get();
    
            if ($penitips->isEmpty()) {
                return response()->json(['message' => 'No tokens found'], 404);
            }
    
            $credentials = config('firebase.projects.app.credentials.file');
            $firebase = (new Factory)->withServiceAccount($credentials);
            $messaging = $firebase->createMessaging();
    
            foreach ($penitips as $penitip) {
                $message = CloudMessage::withTarget('token', $penitip->fcm_token)
                    ->withNotification([
                        'title' => $request->title,
                        'body' => $request->body,
                    ]);
    
                try {
                    $messaging->send($message);
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to ' . $penitip->fcm_token . ': ' . $e->getMessage());
                }
            }
    
            return response()->json(['message' => 'Notification sent']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function updateFcmToken(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'fcm_token' => 'required|string',
                'user_type' => 'required|string|in:pembeli,penitip,pegawai',
            ]);

            $userTypes = [
                'pembeli' => Pembeli::class,
                'penitip' => Penitip::class,
                'pegawai' => Pegawai::class,
            ];

            $model = $userTypes[$request->user_type];
            $user = $model::find($request->id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->fcm_token = $request->fcm_token;
            $user->save();

            return response()->json(['message' => 'FCM token updated']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating FCM token'], 500);
        }
    }

    public function sendWelcomeNotification(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            $credentials = config('firebase.projects.app.credentials.file');
            if (!file_exists($credentials)) {
                throw new \Exception('Firebase credentials file does not exist at: ' . $credentials);
            }

            $firebase = (new Factory)->withServiceAccount($credentials);
            $messaging = $firebase->createMessaging();

            $message = CloudMessage::withTarget('token', $request->fcm_token)
                ->withNotification([
                    'title' => $request->title,
                    'body' => $request->body,
                ]);

            $messaging->send($message);
            \Log::info('Welcome notification sent to token: ' . $request->fcm_token);

            return response()->json(['message' => 'Notification sent']);
        } catch (\Exception $e) {
            \Log::error('Error sending welcome notification: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send notification: ' . $e->getMessage()], 500);
        }
    }    
}