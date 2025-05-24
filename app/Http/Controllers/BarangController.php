<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Penitipan;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BarangController extends Controller
{
    public function index()
    {
        try {
            $barangs = Barang::where('status_barang', 'tersedia')->get();
            $barangs = $barangs->map(function ($barang) {
                $barang->foto = asset($barang->foto);
                return $barang;
            });
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $barangs,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $barang = Barang::find($id);
        $barang->foto = asset($barang->foto);

        if ($barang) {
            return response()->json([
                'status' => true,
                'message' => 'Data Barang',
                'data' => $barang
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Barang not found'
            ], 404);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'id_penitipan' => 'required|integer |exists:penitipan,id_penitipan',
            'id_kategori' => 'required|integer |exists:kategori,id_kategori',
            'id_hunter' => 'required|integer |exists:pegawai,id_pegawai',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:1000',
            'berat' => 'required|numeric',
            'isGaransi' => 'required|boolean',
            'akhir_garansi' => 'required|date',
            'status_perpanjangan' => 'required|boolean',
            'harga' => 'required|numeric',
            'tanggal_akhir' => 'required|date',
            'batas_ambil' => 'required|date',
            'status_barang' => 'required|string|max:255',
            'tanggal_ambil' => 'required|date',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('barang', 'public');
        }

        $barang = Barang::create([
            'id_penitipan' => $request->id_penitipan,
            'id_kategori' => $request->id_kategori,
            'id_hunter' => $request->id_hunter,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'berat' => $request->berat,
            'isGaransi' => $request->isGaransi,
            'akhir_garansi' => $request->akhir_garansi,
            'status_perpanjangan' => $request->status_perpanjangan,
            'harga' => $request->harga,
            'tanggal_akhir' => $request->tanggal_akhir,
            'batas_ambil' => $request->batas_ambil,
            'status_barang' => $request->status_barang,
            'tanggal_ambil' => $request->tanggal_ambil,
            'foto' => $fotoPath,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Barang created successfully',
            'data' => $barang
        ], 201);

    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang not found'
            ], 404);
        }
        $validatedData = $request->validate([
            'id_penitipan' => 'sometimes|integer |exists:penitipan,id_penitipan',
            'id_kategori' => 'sometimes|integer |exists:kategori,id_kategori',
            'id_hunter' => 'sometimes|integer |exists:pegawai,id_pegawai',
            'nama_barang' => 'sometimes|string|max:255',
            'harga' => 'sometimes|numeric',
            'stok' => 'sometimes|integer',
            'foto' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $barang->update($validatedData);

        if ($barang->foto !== null) {
            File::delete(public_path($barang->foto));
        }

        $destinationPath = public_path('barang');
        $fotoFile = $request->file('foto');
        $fotoName = 'foto-' . time() . '.' . $fotoFile->getClientOriginalExtension();
        $fotoFile->move($destinationPath, $fotoName);

        $barang->update([
            'foto' => 'barang/' . $fotoName,
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::find($id);
        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang not found'
            ], 404);
        }

        if ($barang->foto !== null) {
            File::delete(public_path($barang->foto));
        }

        $barang->delete();

        return response()->json([
            'status' => true,
            'message' => 'Barang deleted successfully'
        ]);
    }

    public function searchBarang(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([
                'status' => false,
                'message' => 'Query is required',
                'data' => []
            ], 400);
        }
        $barangs = Barang::where('nama', 'LIKE', "%$query%")
            ->where('status_barang', 'tersedia')
            ->get();

        $barangs = $barangs->map(function ($barang) {
            $barang->foto = asset($barang->foto);
            return $barang;
        });
        return response()->json([
            'status' => true,
            'message' => 'Search results',
            'data' => $barangs
        ]);
    }

    public function showBarangIsGaransi()
    {
        $barangs = Barang::where('isGaransi', true)
            ->where('akhir_garansi', '>=', now()->toDateString())
            ->where('status_barang', 'tersedia')
            ->get();


        if ($barangs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No items available for purchase'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Barang yang bisa dibeli',
            'data' => $barangs
        ]);
    }

    public function showBarangIsNotGaransi()
    {
        $barangs = Barang::where('isGaransi', false)
            ->where('status_barang', 'tersedia')
            ->get();

        if ($barangs->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No items available for purchase'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Barang yang bisa dibeli',
            'data' => $barangs
        ]);
    }

    public function relatedProducts($id_kategori)
    {
        if ((int) $id_kategori <= 10) {
            $barang = Barang::where('id_kategori', '<', 10)->get();
        } else {
            $barang = Barang::where('id_kategori', 'like', "$id_kategori%")
                ->where('id_kategori', '>', 10)
                ->get();
        }

        $barang = $barang->where('status_barang', 'tersedia');
        $barang = $barang->map(function ($barang) {
            $barang->foto = asset($barang->foto);
            return $barang;
        });
        if ($barang->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Barang not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Barang',
            'data' => $barang
        ]);
    }

    public function showBarangbyKategori($id_kategori)
    {
        if ($id_kategori === '0') {
            $barang = Barang::where('id_kategori', '<', 10)
                ->where('status_barang', 'tersedia')
                ->get();
        } else {
            $barang = Barang::where('id_kategori', 'like', "$id_kategori%")
                ->where('id_kategori', '>', 10)
                ->where('status_barang', 'tersedia')
                ->get();
        }

        if ($barang->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Barang not found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data Barang',
            'data' => $barang
        ]);
    }

    public function cekBarangGaransibyTanggalGaransi($id_barang)
    {
        try {
            $barang = Barang::find($id_barang);
            if (!$barang) {
                return response()->json([
                    'status' => false,
                    'message' => 'Barang not found'
                ], 404);
            }

            $today = now()->toDateString();
            $garansiAktif = $today <= $barang->akhir_garansi;

            return response()->json([
                'status' => true,
                'garansi_aktif' => $garansiAktif,
                'message' => $garansiAktif ? 'Garansi masih berlaku' : 'Garansi sudah habis',
                'data' => $barang
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to check garansi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function filterBarangByHarga(Request $request)
    {
        $query = Barang::query();

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->whereBetween('harga', [$request->min_price, $request->max_price]);
        } elseif ($request->has('max_price')) {
            $query->where('harga', '<=', $request->max_price);
        } elseif ($request->has('min_price')) {
            $query->where('harga', '>=', $request->min_price);
        }
        $query->where('status_barang', 'tersedia');
        $query->orderBy('harga', 'asc');
        return response()->json($query->get());
    }

}
