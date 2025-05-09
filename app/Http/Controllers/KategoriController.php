<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use Exception;

class KategoriController extends Controller
{
    public function fetchKategori()
    {
        $categories = Kategori::all();

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }
}    
