<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pegawai = Auth::guard('admin')->user();
        if($pegawai && $pegawai->role->nama_role == 'Admin'){
            return $next($request);
        }
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }
}
