<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'id_role' => 'required|integer',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pegawai,email',
            'password' => 'required|string|min:8',
            'tanggal_masuk' => 'required|date',
            'tanggal_lahir' => 'required|date',
        ]);

        $pegawai = Pegawai::create([
            'id_role' => $request->id_role,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tanggal_masuk' => $request->tanggal_masuk,
            'tanggal_lahir' => $request->tanggal_lahir,
        ]);
        
        return response()->json([
            "status" => true,
            "message" => "Pegawai registered successfully",
            "data" => $pegawai
        ], 201);
        
    }

    public function index()
    {
        $pegawai = Pegawai::all();
        if ($pegawai->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No Pegawai found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Pegawai retrieved successfully',
            'data' => $pegawai
        ], 200);
    }

    public function show($id)
    {
        $pegawai = Pegawai::find($id);
        if (!$pegawai) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Pegawai retrieved successfully',
            'data' => $pegawai
        ], 200);
    }

    public function update(Request $request, $pegawaiId)
    {
        $pegawai = Pegawai::find($pegawaiId);
        if (!$pegawai) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai not found',
                'data' => null
            ], 404);
        }
        
        $validatedData = $request->validate([
            'id_role' => 'sometimes|integer|exists:role,id_role',
            'nama' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:pegawai,email,' . $pegawai->id_pegawai,
            'password' => 'sometimes|string|min:8',
            'tanggal_masuk' => 'sometimes|date',
            'tanggal_lahir' => 'sometimes|date',
        ]);

        if ($request->has('password') && !empty($request->password)) {
            $pegawai->password = Hash::make($request->password);
        }    

        $pegawai->update($validatedData);

        return response()->json([
            "status" => true,
            "message" => "Pegawai updated successfully",
            "data" => $pegawai
        ], 200);
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }

        $pegawai->delete();
        return response()->json([
            "status" => true,
            "message" => "Pegawai berhasil dihapus.",
            "data" => null
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        $pegawai = Pegawai::where('nama', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Pencarian pegawai berhasil',
            'data' => $pegawai
        ], 200);
    }

}
