<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Detail_donasi;
use App\Models\Request_donasi;
use App\Models\Barang;
use App\Models\Organisasi;
use App\Http\Controllers\Request_donasiController;
use App\Http\Controllers\OrganisasiController;
use App\Models\Penitipan;
use App\Models\Penitip;
use App\Http\Controllers\PenitipController;
use App\Http\Controllers\PenitipanController;
use Exception;

class Detail_donasiController extends Controller
{
    public function fetchRequest(){
        try{
            $request_donasi = Request_donasi::all();
            return response()->json([
                'status' => true,
                'message' => 'Data Request Donasi',
                'data' => $request_donasi
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data request donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_request' => 'required|integer', 
            'id_barang' => 'required|integer',
            'tanggal_donasi' => 'required|date',
            'nama_penerima' => 'required|string'
        ], [
            'id_request.required' => 'ID Request is required',
            'id_barang.required' => 'ID Barang is required',
            'tanggal_donasi.required' => 'Tanggal Donasi is required',
            'nama_penerima.required' => 'Nama Penerima is required'
        ]);

        try {
            $barang = Barang::findOrFail($validatedData['id_barang']);
            $barang->status_barang = 'donasi';
            $barang->save();

            $request_donasi = Request_donasi::findOrFail($validatedData['id_request']);
            $request_donasi->status_terpenuhi = 1; 
            $request_donasi->save();
            $poin_reward = (int) floor($barang->harga / 10000);

            $detail_donasi = Detail_donasi::create($validatedData);

            $detail_donasi->reward_sosial = $poin_reward;
            $detail_donasi->save();

            $penitipan = Penitipan::where('id_penitipan', $barang->id_penitipan)->first();
            $penitip = Penitip::where('id_penitip', $penitipan->id_penitip)->first();
            $penitip->poin = $penitip->poin + $poin_reward;
            $penitip->save();

            return response()->json([
                'status' => true,
                'message' => 'Detail Donasi created successfully',
                'data' => $detail_donasi
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal membuat detail donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateDonasi(Request $request, $id){
        $validatedData = $request->validate([
            'tanggal_donasi' => 'required|date',
            'nama_penerima' => 'required|string',
        ], [
            'tanggal_donasi.required' => 'Tanggal Donasi is required',
            'nama_penerima.required' => 'Nama Penerima is required',
        ]);

        try{
            
            $detail_donasi = Detail_donasi::findOrFail($id);
            $detail_donasi->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Detail Donasi updated successfully',
                'data' => $detail_donasi
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui detail donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showOrganisasi(){
        try{
            $organisasi = Organisasi::all();
            return response()->json([
                'status' => true,
                'message' => 'Data Organisasi',
                'data' => $organisasi
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data organisasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function historyDonasibyOrganisasi($id){
        try{
            $detail_donasi = Detail_donasi::whereHas('dtDonasiReqDonasi', function($query) use ($id){
                $query->where('id_organisasi', $id);
            })->get();

            return response()->json([
                'status' => true,
                'message' => 'History Donasi by Organisasi',
                'data' => $detail_donasi
            ]);
        } catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil history donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchDetailDonasi(){
        try{
            $detail_donasi = Detail_donasi::with('dtDonasiReqDonasi')->get();
            return response()->json([
                'status' => true,
                'message' => 'Data Detail Donasi',
                'data' => $detail_donasi
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data detail donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchBarangForDonasi(){
        try {
            $barangs = Barang::where('batas_ambil', '>=', Carbon::now()->addDay())
            ->where('status_barang', '!=', 'sold out')->get();
            return response()->json([
                'status' => true,
                'message' => 'Data Barang',
                'data' => $barang
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchAllBarang(){
        try {
            $barang = Barang::all();
            return response()->json([
                'status' => true,
                'message' => 'Data Barang',
                'data' => $barang
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
