<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembeli;
use App\Models\Pembelian;
use App\Models\Keranjang;
use App\Models\Detail_keranjang;
use App\Models\Detail_pembelian;
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

        $pembelian = Pembelian::where('id_pembeli', $pembeli->id_pembeli)
            ->first();
        if (!$pembelian) {
            return response()->json(['error' => 'No purchase history found'], 404);
        }
        $history = Detail_pembelian::with(['pembelian', 'barang'])
            ->whereHas('pembelian', function ($query) use ($pembeli) {
                $query->where('id_pembeli', $pembeli->id_pembeli);
            })
            ->get();

        // $history = Pembelian::with(['keranjang.pembeli'])
        //     ->whereHas('keranjang', function ($query) use ($pembeli) {
        //         $query->where('id_pembeli', $pembeli->id_pembeli);
        //     })
        //     ->get();

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

        $pembelian = Pembelian::where('id_pembelian', $id)
            ->where('id_pembeli', $pembeli->id_pembeli)
            ->first();

        if (!$pembelian) {
            return response()->json(['error' => 'Order not found or unauthorized'], 404);
        }

        $details = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)
            ->with('barang')
            ->get()
            ->map(function ($item) {
                if ($item->barang) {
                    $item->barang->foto = asset('storage/' . $item->barang->foto);
                }
                return $item;
            });

        return response()->json([
            'pembelian' => $pembelian,
            'items' => $details
        ]);
    }


    public function getOrderDetails($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pembeli = Pembeli::where('id_pembeli', $user->id_pembeli)->first();
        if (!$pembeli) {
            return response()->json(['error' => 'Pembeli not found'], 404);
        }

        $pembelian = Pembelian::where('id_pembelian', $id)
            ->where('id_pembeli', $pembeli->id_pembeli)
            ->first();

        if (!$pembelian) {
            return response()->json(['error' => 'Order not found or unauthorized'], 404);
        }

        $details = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)
            ->with('barang')
            ->get()
            ->map(function ($item) {
                if ($item->barang) {
                    $item->barang->foto = asset('storage/' . $item->barang->foto);
                }
                return $item;
            });

        return response()->json([
            'pembelian' => $pembelian,
            'items' => $details
        ]);
    }




}
