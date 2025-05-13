<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembeli;
use App\Models\Pembelian;
use App\Models\Keranjang;
use App\Models\Detail_keranjang;
class PembelianController extends Controller
{
    public function getOrderHistory(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pembeli = Pembeli::where('id_pembeli', $user->id_pembeli)->first();

        if (!$pembeli) {
            return response()->json(['error' => 'Pembeli not found'], 404);
        }

        $history = Pembelian::with(['keranjang.pembeli'])
            ->whereHas('keranjang', function ($query) use ($pembeli) {
                $query->where('id_pembeli', $pembeli->id_pembeli);
            })
            ->get();

        return response()->json(['data' => $history]);
    }

    public function getOrderHistoryById($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pembeli = Pembeli::where('id_pembeli', $user->id_pembeli)->first();

        if (!$pembeli) {
            return response()->json(['error' => 'Pembeli not found'], 404);
        }

        $history = Pembelian::with(['keranjang.pembeli'])
            ->where('id_pembelian', $id)
            ->whereHas('keranjang', function ($query) use ($pembeli) {
                $query->where('id_pembeli', $pembeli->id_pembeli);
            })
            ->first();

        if (!$history) {
            return response()->json(['error' => 'Order not found or unauthorized'], 404);
        }

        return response()->json($history);
    }


    public function getOrderDetails($id)
    {

        // Get the id_keranjang for the given pembelian
        $idKeranjang = Pembelian::where('id_pembelian', $id)->value('id_keranjang');

        // Now fetch detail_keranjang records with barang relation
        $items = Detail_keranjang::with('barang')
            ->where('id_keranjang', $idKeranjang)
            ->get();


        return response()->json([
            'items' => $items->map(function ($item) {
                $item->barang->foto_url = asset('storage/' . $item->barang->foto);
                return $item;
            }),
        ]);
    }




}
