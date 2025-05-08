<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alamat;
use App\Models\Pembeli;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AlamatController extends Controller
{

    public function fetchAlamat()
    {
        try {
            $pembeli = auth('pembeli')->user();
            if (!$pembeli) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }

            $alamat = Alamat::where('id_pembeli', $pembeli->id_pembeli)->get();

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Address fetched successfully',
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
    public function addAlamat(Request $request){
        try{

            $pembeli = auth('pembeli')->user();
            if (!$pembeli) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }

            $request->validate([
                'nama_alamat' => 'required|string|max:255',
                'nama_jalan' => 'required|string|max:255',
                'nama_kota' => 'required|string|max:255',
                'kode_pos' => 'required|integer|maxdigits:11',
            ]);

            $alamatUtama = Alamat::where('id_pembeli', $pembeli->id_pembeli)->where('isUtama', true)->first();
            if($alamatUtama){
                $setAlamatUtama = false;
            }else if(!$alamatUtama){
                $setAlamatUtama = true;
            }

            $alamat = Alamat::create([
                'id_pembeli' => $pembeli->id_pembeli,
                'nama_alamat' => $request->nama_alamat,
                'nama_jalan' => $request->nama_jalan,
                'nama_kota' => $request->nama_kota,
                'kode_pos' => $request->kode_pos,
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


    public function updateAlamat(Request $request, $id_alamat)
    {
        try {
            $pembeli = auth('pembeli')->user();
            if (!$pembeli) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }

            $alamat = Alamat::findOrFail($id_alamat);

            if ($alamat->id_pembeli !== $pembeli->id_pembeli) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }
        


            $request->validate([
                'nama_alamat' => 'required|string|max:255',
                'nama_jalan' => 'required|string|max:255',
                'nama_kota' => 'required|string|max:255',
                'kode_pos' => 'required|integer|maxdigits:11',
            ]);

            $alamat->update($request->all());

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Address updated successfully',
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAlamat($id_alamat){
        $pembeli = auth('pembeli')->user();
        if (!$pembeli) {
            return response()->json([
                'message' => 'User not authenticated',
            ], 401);
        }
        try {
            $alamat = Alamat::findOrFail($id_alamat);

            if ($alamat->id_pembeli !== $pembeli->id_pembeli) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $alamat->delete();
            $alamat = Alamat::where('id_pembeli', $pembeli->id_pembeli)->first();
            if($alamat){
                $alamat->update(['isUtama' => true]);
            }
            return response()->json([
                'message' => 'Address deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setUtama($id_alamat)
    {
        try {
            $pembeli = auth('pembeli')->user();
            if (!$pembeli) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }

            $alamat = Alamat::findOrFail($id_alamat);

            if ($alamat->id_pembeli !== $pembeli->id_pembeli) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            Alamat::where('id_pembeli', $pembeli->id_pembeli)->update(['isUtama' => false]);
            $alamat->update(['isUtama' => true]);

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Address set as utama successfully',
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to set address as utama',
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
            ], 200, [], JSON_UNESCAPED_SLASHES);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menemukan alamat utama',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
