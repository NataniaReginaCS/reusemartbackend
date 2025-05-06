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

    public function findUtama(Request $request){
        try{
            $request->validate([
                'id_pembeli'=> 'required'
            ]);
            
            $alamatUtama = Alamat::where('id_pembeli', $request->id_pembeli)->where('isUtama', true)->first();
            
            return response()->json([
                'alamatUtama' => $alamatUtama,
                'message' => 'Address  find sucessfully',
            ], 201, [], JSON_UNESCAPED_SLASHES);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Failed to find priority address',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }
}
