<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang;
use App\Models\Barang;
use App\Models\Pembeli;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class KeranjangController extends Controller
{
    public function fetchKeranjang () {
        try {
            $user = auth::guard('pembeli')->user();

            $keranjang = Keranjang::where('id_pembeli', $user->id_pembeli)
                ->with([
                    'barang.barangPenitipan.penitipanPenitip' // Nested eager loading
                ])
                ->get();

            return response()->json([
                'status' => 'success',
                'keranjang' => $keranjang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function addToKeranjang($idBarang) {
        try {
            $user = auth::guard('pembeli')->user();
            $barang = Barang::find($idBarang);
            if (!$barang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang not found'
                ], 404);
            }

            $existingBarang = Keranjang::where('id_barang', $idBarang)
                ->where('id_pembeli', $user->id_pembeli)
                ->first();

            if ($existingBarang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item already in cart'
                ], 400);
            }

            Keranjang::create([
                'id_pembeli' => $user->id_pembeli,
                'id_barang' => $idBarang,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang added to keranjang successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteKeranjang($idBarang) {
        try {
            $user = auth::guard('pembeli')->user();
            $keranjang = Keranjang::where('id_barang', $idBarang)
                ->where('id_pembeli', $user->id_pembeli)
                ->first();

            if (!$keranjang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item not found in cart'
                ], 404);
            }

            $keranjang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from cart successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
