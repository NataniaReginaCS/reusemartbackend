<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisasi;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class OrganisasiController extends Controller
{
    public function fetchOrganisasi(Request $request)
    {
        try {
            $organisasi = Organisasi::all();
            return response()->json([
                'organisasi' => $organisasi,
                'message' => 'Data retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateOrganisasi(Request $request, $id_organisasi)
    {
        try {
            $organisasi = Organisasi::findOrFail($id_organisasi);
        
            $request->validate([
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string',
                'telp' => 'required|string',
                'email' => 'required|string|email|max:255|unique:organisasi,email,' . $id_organisasi.',id_organisasi',
                'foto' => 'required|image:jpeg,png,jpg,gif,svg|max:2048',
            ],
            [
                'email.required' => 'Email is required',
                'email.email' => 'Email must be a valid email address',
                'email.max' => 'Email must not exceed 255 characters',
                'foto.max' => 'Picture must not exceed 2 mb',
                'foto.image' => 'Picture must be an image',
                'foto.mimes' => 'Picture must be a file of type: jpeg, png, jpg, gif, svg',
                'nama.required' => 'Name is required',
                'alamat.required' => 'Address is required',
                'telp.required' => 'Phone number is required',
            ]);

            if($request->password == NULL){
                $request->password = $organisasi->password;
            }

            $cekEmail = Organisasi::where('email', $request->email)->where('id_organisasi', '!=', $id_organisasi)->exists() ||
            DB::table('pembeli')->where('email', $request->email)->exists() ||
            DB::table('penitip')->where('email', $request->email)->exists();

            if ($cekEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 400);
            }

            if($request->hasFile('foto')) {
                if ($organisasi->foto) {
                    Storage::disk('public')->delete($organisasi->foto);
                }
                $foto = $request->file('foto');
                $fotoPath = $foto->store('images/organisasi', 'public');
            
            }

            $organisasi->update([ 
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'telp' => $request->telp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'foto' => $fotoPath,
            ]);

            return response()->json([
                'organisasi' => $organisasi,
                'message' => 'Organisasi updated successfully',
            ], 200);
        } catch(ValidationException $e) {
            return response()->json([
                'message' => 'Failed to update organisasi',
                'errors' => $e->errors(),
            ], 422);
        }catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update organisasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteOrganisasi($id_organisasi)
    {
        try {
            $organisasi = Organisasi::findOrFail($id_organisasi);
            if ($organisasi->foto) {
                Storage::disk('public')->delete($organisasi->foto);
            }
            $organisasi->delete();
            return response()->json([
                'message' => 'Organisasi deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete organisasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
