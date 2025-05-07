<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisasi;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
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
                'password' => 'nullable|string',
                'foto' => 'nullable|string|max:255',
            ]);
        
            // if ($request->hasFile('foto')) {
            //     if ($organisasi->foto && File::exists(public_path($organisasi->foto))) {
            //         File::delete(public_path($organisasi->foto));
            //     }
            //     $fotoPath = $request->file('foto')->store('profile', 'public');
            // } else {
            //     $fotoPath = $organisasi->foto;
            // }


            $organisasi->update([ 
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'telp' => $request->telp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'foto' => 'ambatukam',
            ]);

            return response()->json([
                'organisasi' => $organisasi,
                'message' => 'Organisasi updated successfully',
            ], 200);
        } catch (Exception $e) {
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
