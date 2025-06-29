<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandise;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MerchandiseController extends Controller
{
    public function fetchMerchandise()
    {
        try {
            $merchandise = DB::table('merchandise')->get();
            $merchandise = $merchandise->map(function ($merchandise) {
                $merchandise->foto = asset('storage/' . $merchandise->foto);
                return $merchandise;
            });
            return response()->json(['status' => 'success', 'data' => $merchandise]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchMerchandiseById($idMerchandise)
    {
        try {
            $merchandise = DB::table('merchandise')
                ->where('id_merchandise', $idMerchandise)
                ->first();
            if ($merchandise) {
                $merchandise->foto = asset('storage/' . $merchandise->foto);

            }

            return response()->json(data: ['status' => 'success', 'data' => $merchandise]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function claimMerchandise($id_merchandise)
    {
    
        try{
            $merchandise = DB::table('merchandise')
                ->where('id_merchandise', $id_merchandise)
                ->first();

            $pembeli = Auth::guard('pembeli')->user();

            if (!$merchandise || !$pembeli) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pembeli atau merchandise tidak ditemukan.'
                ], 404);
            }

            if ($pembeli->poin < $merchandise->poin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Poin tidak cukup untuk menukarkan merchandise ini.'
                ], 400);
            }

            DB::beginTransaction();

            DB::table('pembeli')
                ->where('id_pembeli', $pembeli->id_pembeli)
                ->update([
                    'poin' => $pembeli->poin - $merchandise->poin,
                ]);

            DB::table('merchandise')
                ->where('id_merchandise', $merchandise->id_merchandise)
                ->update([
                    'stok' => $merchandise->stok - 1,
                ]);
            $day = now()->addDays(1);
            DB::table('penukaran_poin')
                ->insert([
                    'id_pembeli' => $pembeli->id_pembeli,
                    'id_merchandise' => $merchandise->id_merchandise,
                    'tanggal_penukaran' => now(),
                    'status_verifikasi' => '1',
                    'tanggal_ambil' => $day,
                ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penukaran merchandise berhasil!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses penukaran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
