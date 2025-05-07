<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alamat;
use Exception;
use Illuminate\Support\Facades\Auth;

class AlamatController extends Controller
{
    public function fetchAlamat(Request $request)
    {
        try {
            $alamat = Alamat::all();
            return response()->json([
                'alamat' => $alamat,
                'message' => 'Data retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAlamat(Request $request)
    {
        try {
            $request->validate([
                'id_pembeli' => 'required|integer',
                'nama_alamat' => 'required|string|max:255',
                'nama_jalan' => 'required|string|max:255',
                'nama_kota' => 'required|string|max:255',
                'kode_pos' => 'required|string|max:10',
                'isUtama' => 'required|boolean',
            ]);

            $alamat = Alamat::create([
                'id_pembeli' => $request->id_pembeli,
                'nama_alamat' => $request->nama_alamat,
                'nama_jalan' => $request->nama_jalan,
                'nama_kota' => $request->nama_kota,
                'kode_pos' => $request->kode_pos,
                'isUtama' => $request->isUtama,
            ]);

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Data added successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAlamat(Request $request, $id_alamat)
    {
        try {
            $alamat = Alamat::findOrFail($id_alamat);

            $request->validate([
                'nama_alamat' => 'required|string|max:255',
                'nama_jalan' => 'required|string|max:255',
                'nama_kota' => 'required|string|max:255',
                'kode_pos' => 'required|string|max:10',
                'isUtama' => 'required|boolean',
            ]);

            $alamat->update([
                'nama_alamat' => $request->nama_alamat,
                'nama_jalan' => $request->nama_jalan,
                'nama_kota' => $request->nama_kota,
                'kode_pos' => $request->kode_pos,
                'isUtama' => $request->isUtama,
            ]);

            return response()->json([
                'alamat' => $alamat,
                'message' => 'Data updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
