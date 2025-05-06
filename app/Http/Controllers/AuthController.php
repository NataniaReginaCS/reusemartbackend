<?php

namespace App\Http\Controllers;

use App\Models\Pembeli;
use App\Models\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    public function registerPembeli(Request $request){
        try{

            $request->validate([
                'nama' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:pembeli',
                'password' => 'required|string',
                'telepon' => 'required|string|unique:pembeli',
                'foto' => 'nullable|string|max:255',
            ]);

            $fotoPath = $request->foto ? $request->foto : 'profile/default.png';
        
            $pembeli = Pembeli::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => $request->password,
                'telepon' => $request->telepon,
                'poin' => 0,
                'foto' => $fotoPath,
            ]);
            
            $keranjang = Keranjang::create([
                'id_pembeli' => $pembeli->id_pembeli,
            ]);
            
            return response()->json([
                'pembeli' => $pembeli,
                'message' => 'User  registered sucessfully',
            ], 201, [], JSON_UNESCAPED_SLASHES);
        }catch(Exception $e){
            return response()->json([
                'message' => 'Failed to register user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
}
