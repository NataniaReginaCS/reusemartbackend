<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Pembeli;
use App\Models\Alamat;
use App\Models\Organisasi;
use App\Mail\ResetPasswordLinkMail;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required',
            ]);
    
            $userType = null;
            $user = null;
        
            foreach ([
                'pembeli' => \App\Models\Pembeli::class,
                'penitip' => \App\Models\Penitip::class,
                'organisasi' => \App\Models\Organisasi::class
            ] as $type => $model) {
                $user = $model::where('email', $request->email)->first();
                if ($user) {
                    $userType = $type;
                    break;
                }
            }
    
            if (!$user || !$userType) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            $token = Str::random(64);
    
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email, 'user_type' => $userType],
                ['token' => $token, 'created_at' => now()]
            );
    
            $resetLink = "https://reusemartkl.barioth.web.id/reset-password?email={$request->email}&token=$token&user_type=$userType";
    
            Mail::to($request->email)->send(new ResetPasswordLinkMail($resetLink));
    
            return response()->json(['message' => 'Link reset password telah dikirim.']);
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    


    private function getModel($type)
    {
        return match ($type) {
            'pembeli' => \App\Models\Pembeli::class,
            'penitip' => \App\Models\Penitip::class,
            'organisasi' => \App\Models\Organisasi::class,
            default => abort(400, 'Invalid user type'),
        };
    }
}
