<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Penitipan;
use App\Models\Penitip;
use App\Models\Pembeli;
use App\Models\Barang;
use Exception;

class RatingController extends Controller
{
    public function fetchRating()
    {
        $ratings = Rating::all();
        if ($ratings->isEmpty()) {
            return response()->json(['message' => 'No ratings found'], 404);
        }

        return response()->json(['ratings' => $ratings], 200);
    }

    public function createRating(Request $request)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'id_pembeli' => 'required|exists:pembeli,id_pembeli',
            'rating_diberikan' => 'required|integer|min:1|max:5',
        ]);

        $rating = new Rating();
        $rating->id_barang = $request->id_barang;
        $barang = Barang::find($request->id_barang);
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }
        $penitipan = Penitipan::where('id_penitipan', $barang->id_penitipan)->first();
        if (!$penitipan) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }
        $penitip = Penitip::where('id_penitip', $penitipan->id_penitip)->first();
        if (!$penitip) {
            return response()->json(['message' => 'Penitip tidak ditemukan'], 404);
        }

        $rating->id_barang = $request->id_barang;
        $rating->id_penitip = $penitip->id_penitip;
        $rating->id_pembeli = $request->id_pembeli;
        $rating->rating_diberikan = $request->rating_diberikan;
        $rating->save();

        $pembeli = Pembeli::find($request->id_pembeli);
        if (!$pembeli) {
            return response()->json(['message' => 'Pembeli tidak ditemukan'], 404);
        }

        $totalRating = Rating::where('id_penitip', $penitip->id_penitip)
            ->sum('rating_diberikan');
        $ratingCount = Rating::where('id_penitip', $penitip->id_penitip)
            ->count('rating_diberikan');
        $averageRating = 0;

        if ($ratingCount > 0) {
            $averageRating = $totalRating / $ratingCount;
        } else {
            $averageRating = $request->rating_diberikan;
        }

        $penitip->total_rating = $averageRating;
        $penitip->save();

        return response()->json(['message' => 'Rating berhasil ditambahkan'], 201);
    }

    public function getRating($id_barang)
    {
        $rating = Rating::where('id_barang', $id_barang)->get();
        if ($rating->isEmpty()) {
            return response()->json(['message' => 'No ratings found'], 404);
        }

        return response()->json(['data' => $rating], 200);
    }
}
