<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'user_type' => 'required|in:pembeli,penitip,organisasi',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        $model = $this->getModel($request->user_type);
        $user = $model::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('user_type', $request->user_type)
            ->delete();

        return response()->json(['message' => 'Password has been reset successfully.']);
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

