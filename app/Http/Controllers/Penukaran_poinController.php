<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembeli;
use App\Models\Merchandise;
use App\Models\Penukaran_poin;
use Exception;
use Carbon\Carbon;

class Penukaran_poinController extends Controller
{
    public function getPenukaranPoin() 
    {
        try{
            $penukaranPoin = Penukaran_poin::with(['penPoinPembeli', 'penPoinMerchandise'])->get();
            return response()->json([
                'data' => $penukaranPoin,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve penukaran poin data'], 500);
        }
    }

    public function updatePenukaranPoin(Request $request, $id) 
    {
        try {
            $request->validate([
                'status_verifikasi' => 'required|string',
                'tanggal_ambil' => 'nullable|date',
            ]);
            $penukaranPoin = Penukaran_poin::findOrFail($id);
            $penukaranPoin->status_verifikasi = $request->status_verifikasi;
            if($request->tanggal_ambil){
                $penukaranPoin->tanggal_ambil = Carbon::parse($request->tanggal_ambil);
            }
            $penukaranPoin->save();

            if ($request->status_verifikasi === 'Approved') {
                $merchandise = Merchandise::findOrFail($penukaranPoin->id_merchandise);
                $merchandise->stok -= 1; 
                $merchandise->save();
            }

            $pembeli = Pembeli::findOrFail($penukaranPoin->id_pembeli);
            $pembeli->poin -= $penukaranPoin->penPoinMerchandise->poin; 
            $pembeli->save();

            return response()->json([
                'message' => 'Penukaran poin updated successfully',
                'penukaran_poin' => $penukaranPoin,
                'merchandise' => $penukaranPoin->penPoinMerchandise,
                'pembeli' => $penukaranPoin->penPoinPembeli,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update penukaran poin: ' . $e->getMessage()], 500);
        }
    }
}
