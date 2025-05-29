<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Storage;
use App\Models\Penitipan;
use App\Models\Barang;
use App\Models\Pegawai;
use App\Models\Penitip;

class PenitipanController extends Controller
{
    public function index()
    {
        try {
            $penitipan = Penitipan::all();

            return response()->json([
                'status' => 'success',
                'data' => $penitipan
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch penitipan data'], 500);
        }
    }

    public function show($id)
    {
        try {
            $penitipan = Penitipan::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $penitipan
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Penitipan not found'], 404);
        }
    }

    public function showPenitip($id)
    {
        try {
            $penitipan = Penitipan::findOrFail($id);
            $penitip = $penitipan->penitipanPenitip;

            return response()->json([
                'status' => 'success',
                'data' => $penitip
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Penitipan not found'], 404);
        }
    }

    public function showPegawai($id)
    {
        try {
            $penitipan = Penitipan::findOrFail($id);
            $pegawai = $penitipan->penitipanPegawai;

            return response()->json([
                'status' => 'success',
                'data' => $pegawai
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Penitipan not found'], 404);
        }
    }   

    public function showBarang($id)
    {
        try {
            $penitipan = Penitipan::findOrFail($id);
            $barang = $penitipan->penitipanBarang;

            if ($barang) {
                $barang->foto = asset('storage/' . $barang->foto);
            }

            $durasi_penitipan = now()->diffInDays($barang->tanggal_akhir, false);
            $barang->durasi_penitipan = max(0, $durasi_penitipan);

            return response()->json([
                'status' => 'success',
                'data' => $barang
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Penitipan not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_penitip' => 'required',
                'id_pegawai' => 'required',
                'tanggal_masuk' => 'required|date',
                'id_kategori' => 'required',
                'id_hunter' => 'nullable',
                'nama' => 'required|string',
                'deskripsi' => 'required|string',
                'foto' => 'required|image',
                'berat' => 'required|numeric',
                'isGaransi' => 'required|boolean',
                'akhir_garansi' => 'nullable|date',
                'harga' => 'required|numeric',
                'tanggal_ambil' => 'nullable|date',
            ]);
            $tanggal_masuk = \Carbon\Carbon::parse($request->tanggal_masuk);
            $tanggal_akhir = $tanggal_masuk->copy()->addDays(30);
            $batas_ambil = $tanggal_akhir->copy()->addDays(7);
            
            $durasi_penitipan = $tanggal_masuk->diffInDays($tanggal_akhir, false);
            $durasi_penitipan = max(0, (int) $durasi_penitipan);

            $penitipan = Penitipan::create([
                'id_penitip' => $request->id_penitip,
                'id_pegawai' => $request->id_pegawai,
                'tanggal_masuk' => $tanggal_masuk,
            ]);

            $foto = $request->file('foto');
            $fotoPath = $foto->store('images/barang', 'public');

            $barang = Barang::create([
                'id_penitipan' => $penitipan->id_penitipan,
                'id_kategori' => $request->id_kategori,
                'id_hunter' => $request->id_hunter,
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'foto' => $fotoPath,
                'berat' => $request->berat,
                'isGaransi' => $request->isGaransi,
                'akhir_garansi' => $request->akhir_garansi,
                'status_perpanjangan' => $request->status_perpanjangan,
                'harga' => $request->harga,
                'tanggal_akhir' => $tanggal_akhir,
                'batas_ambil' => $batas_ambil,
                'status_barang' => $request->status_barang,
                'tanggal_ambil' => $request->tanggal_ambil,
                'durasi_penitipan' => $durasi_penitipan,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'penitipan' => $penitipan,
                    'barang' => $barang,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to store data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeBarang(Request $request, $id_penitipan)
    {
        try {
            $request->validate([
                'id_kategori' => 'required',
                'id_hunter' => 'nullable',
                'nama' => 'required|string',
                'deskripsi' => 'required|string',
                'foto' => 'required|image',
                'berat' => 'required|numeric',
                'isGaransi' => 'required|boolean',
                'akhir_garansi' => 'nullable|date',
                'harga' => 'required|numeric',
                'tanggal_ambil' => 'nullable|date',
            ]);

            $penitipan = Penitipan::findOrFail($id_penitipan);

            if (!$penitipan) {
                return response()->json(['error' => 'Penitipan not found'], 404);
            }

            $tanggal_masuk = \Carbon\Carbon::parse($penitipan->tanggal_masuk);
            $tanggal_akhir = $tanggal_masuk->copy()->addDays(30);
            $batas_ambil = $tanggal_akhir->copy()->addDays(7);
            
            $durasi_penitipan = $tanggal_masuk->diffInDays($tanggal_akhir, false);
            $durasi_penitipan = max(0, (int) $durasi_penitipan);

            $foto = $request->file('foto');
            $fotoPath = $foto->store('images/barang', 'public');

            $barang = Barang::create([
                'id_penitipan' => $penitipan->id_penitipan,
                'id_kategori' => $request->id_kategori,
                'id_hunter' => $request->id_hunter,
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'foto' => $fotoPath,
                'berat' => $request->berat,
                'isGaransi' => $request->isGaransi,
                'akhir_garansi' => $request->akhir_garansi,
                'status_perpanjangan' => $request->status_perpanjangan,
                'harga' => $request->harga,
                'tanggal_akhir' => $tanggal_akhir,
                'batas_ambil' => $batas_ambil,
                'status_barang' => $request->status_barang,
                'tanggal_ambil' => $request->tanggal_ambil,
                'durasi_penitipan' => $durasi_penitipan,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'penitipan' => $penitipan,
                    'barang' => $barang,
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to store data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePenitipan(Request $request, $id)
    {
        try {
            $request->validate([
                'id_penitip' => 'sometimes|exists:penitip,id_penitip',
                'id_pegawai' => 'sometimes|exists:pegawai,id_pegawai',
                'tanggal_masuk' => 'sometimes|date',
            ]);

            $penitipan = Penitipan::findOrFail($id);

            if ($penitipan) {
                if ($request->filled('tanggal_masuk')) {
                    $penitipan->tanggal_masuk = \Carbon\Carbon::parse($request->tanggal_masuk);
                    $barangs = Barang::where('id_penitipan', $penitipan->id_penitipan)->get();
                    foreach ($barangs as $barang) {
                        $barang->tanggal_akhir = $penitipan->tanggal_masuk->copy()->addDays(30);
                        $barang->batas_ambil = $barang->tanggal_akhir->copy()->addDays(7);
                        $barang->save();
                    }
                }
                $penitipan->fill($request->only([
                    'id_penitip',
                    'id_pegawai',
                ]));
                $penitipan->save();
            }

            return response()->json([
                'status' => 'success',
                'data' => $barang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update penitipan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'id_penitip' => 'sometimes|exists:penitip,id_penitip',
                'id_pegawai' => 'nullable|exists:pegawai,id_pegawai',
                'tanggal_masuk' => 'sometimes|date',
                'id_kategori' => 'sometimes|exists:kategori,id_kategori',
                'id_hunter' => 'sometimes',
                'nama' => 'sometimes|string',
                'deskripsi' => 'sometimes|string',
                'foto' => 'nullable',
                'berat' => 'sometimes|numeric',
                'isGaransi' => 'sometimes|boolean',
                'akhir_garansi' => 'sometimes|date',
                'status_perpanjangan' => 'sometimes|boolean',
                'harga' => 'sometimes|numeric',
                'tanggal_akhir' => 'sometimes|date',
                'batas_ambil' => 'sometimes|date',
                'status_barang' => 'sometimes|string',
                'tanggal_ambil' => 'sometimes|date',
                'durasi_penitipan' => 'sometimes|integer',
            ]);
            $barang = Barang::findOrFail($id);
    
            if ($barang) {
                if($request->status_perpanjangan == 1){
                    $barang->status_perpanjangan = 1;
                    $barang->tanggal_akhir = now()->addDays(30);
                    $barang->batas_ambil = now()->addDays(37);
                    $barang->save();
                }
    
                if ($request->hasFile('foto')) {
                    if ($barang->foto) {
                        Storage::disk('public')->delete($barang->foto);
                    }
                    $foto = $request->file('foto');
                    $fotoPath = $foto->store('images/barang', 'public');
                    $barang->foto = $fotoPath;
                }
    
                $barang->durasi_penitipan = max(0, now()->diffInDays(\Carbon\Carbon::parse($barang->tanggal_akhir), false));
    
                $barang->fill($request->only([
                    'id_kategori',
                    'id_hunter',
                    'nama',
                    'deskripsi',
                    'berat',
                    'isGaransi',
                    'akhir_garansi',
                    'status_perpanjangan',
                    'harga',
                    'status_barang',
                    'tanggal_ambil',
                ]));
    
                $barang->save();
            }
    
            return response()->json([
                'status' => 'success',
                'data' => $barang
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update penitipan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showAllBarang()
    {
        try{
            $barang = Barang::all();
            foreach ($barang as $item) {
                $item->foto = asset('storage/' . $item->foto);
                $durasi_penitipan = now()->diffInDays($item->tanggal_akhir, false);
                $item->durasi_penitipan = max(0, (int) $durasi_penitipan);
            }


            return response()->json([
                'status' => 'success',
                'data' => $barang
            ], 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch all barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function fetchPenitipPenitipan()
    {
        try {
            $penitip = Penitip::all();
            
            return response()->json([
                'penitip' => $penitip,
                'message' => 'Data retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchPegawaiPenitipan()
    {
        try{
            $pegawai = Pegawai::all();
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $pegawai
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getHistoryPenitipan($id){
        try {
            $penitipan = Penitipan::with(['penitipanBarang', 'penitipanPenitip', 'penitipanPegawai'])
                ->where('id_penitip', $id)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $penitipan
                
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch penitipan history'], 500);
        }
    }
}
