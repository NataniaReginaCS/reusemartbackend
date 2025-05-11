<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request_donasi;
use Exception;
use Illuminate\Support\Facades\Auth;

class Request_donasiController extends Controller
{
    public function index()
    {
        $request_donasi = Request_donasi::all();
        return response()->json([
            'status' => true,
            'message' => 'Data Request Donasi',
            'data' => $request_donasi
        ]);
    }

    public function show(Request $request)
    {

        $organisasi = $request->user();
        $request_donasi = Request_donasi::where('id_organisasi', $organisasi->id_organisasi)->get();
        if ($request_donasi) {
            return response()->json([
                'status' => true,
                'message' => 'Data Request Donasi',
                'data' => $request_donasi
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Request Donasi not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tanggal_request' => 'required|date',
            'deskripsi' => 'required|string|max:255',
        ]);

        try {
            // Get the logged-in user's organisasi ID
            $user = auth()->user();
            $validatedData['id_organisasi'] = $user->id_organisasi;

            $request_donasi = Request_donasi::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Request Donasi created successfully',
                'data' => $request_donasi
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create Request Donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function alokasi(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status_terpenuhi' => 'required|boolean',
        ]);

        try {
            $request_donasi = Request_donasi::find($id);
            if ($request_donasi) {
                $request_donasi->update($validatedData);
                return response()->json([
                    'status' => true,
                    'message' => 'Barang berhasil dialokasikan ke organisasi',
                    'data' => $request_donasi
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Request Donasi tidak ditemukan'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengalokasikan barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'id_organisasi' => 'sometimes|required|integer|exists:organisasi,id_organisasi',
            'tanggal_request' => 'sometimes|required|date',
            'status_terpenuhi' => 'sometimes|required|boolean',
            'deskripsi' => 'sometimes|required|string|max:255',
        ]);

        try {
            $request_donasi = Request_donasi::find($id);
            if ($request_donasi) {
                $request_donasi->update($validatedData);
                return response()->json([
                    'status' => true,
                    'message' => 'Request Donasi updated successfully',
                    'data' => $request_donasi
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Request Donasi not found'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Request Donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $request_donasi = Request_donasi::find($id);
            if ($request_donasi) {
                $request_donasi->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Request Donasi deleted successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Request Donasi not found'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Request Donasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $request_donasi = Request_donasi::where('deskripsi', 'LIKE', "%$query%")->get();

        return response()->json([
            'status' => true,
            'message' => 'Search results',
            'data' => $request_donasi
        ]);
    }
    public function filterByDate(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $request_donasi = Request_donasi::whereBetween('tanggal_request', [$startDate, $endDate])->get();

        return response()->json([
            'status' => true,
            'message' => 'Filtered Request Donasi',
            'data' => $request_donasi
        ]);
    }
    public function filterByStatus(Request $request)
    {
        $status = $request->input('status_terpenuhi');

        $request_donasi = Request_donasi::where('status_terpenuhi', $status)->get();

        return response()->json([
            'status' => true,
            'message' => 'Filtered Request Donasi',
            'data' => $request_donasi
        ]);
    }

    public function historyByOrganisasi($id_organisasi)
    {
        $request_donasi = Request_donasi::where('id_organisasi', $id_organisasi)->get();

        return response()->json([
            'status' => true,
            'message' => 'History Donasi untuk Organisasi',
            'data' => $request_donasi
        ]);
    }

}
