<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembeli;
use App\Models\Alamat;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PembeliController extends Controller
{
    
    public function fetchPembeli()
    {
        $user = Auth::guard('pembeli')->user();
        return response()->json($user, 200);
    }

    public function addAlamat(Request $request){
        try{

            // $user = Auth::user();
            
            // if(!$user){
            //     return response()->json(['message' => 'User tidak ditemukan'], 403);
            // }

            $request->validate([
                'id_pembeli' => 'required',
                'nama_alamat' => 'required|string|max:255'
            ]);

            $alamatUtama = Alamat::where('id_pembeli', $request->id_pembeli)->where('isUtama', true)->first();
            if($alamatUtama){
                $setAlamatUtama = false;
            }else if(!$alamatUtama){
                $setAlamatUtama = true;
            }

            $alamat = Alamat::create([
                'id_pembeli' => $request->id_pembeli,
                'nama_alamat' => $request->nama_alamat,
                'isUtama' => $setAlamatUtama
            ]);

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Address  registered sucessfully',
            ], 201, [], JSON_UNESCAPED_SLASHES);
            
        }catch(Exception $e){
            return response()->json([
                'message' => 'Failed to create address',
                'error' => $e->getMessage(),
            ], 500);
        }
        

    }

    public function findUtama()
    {
        try {
            $pembeli = auth('pembeli')->user();
    
            if (!$pembeli) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }
    
            $alamatUtama = Alamat::where('id_pembeli', $pembeli->id_pembeli)
                                ->where('isUtama', true)
                                ->first();
    
            return response()->json([
                'alamatUtama' => $alamatUtama,
                'message' => 'Alamat utama berhasil ditemukan',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menemukan alamat utama',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
