<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penitip;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class PenitipController extends Controller
{

    public function addPenitip(Request $request)
    {
        try {
            $request->validate(
                [
                    'nama' => 'required|string|max:255',
                    'telepon' => 'required|string',
                    'email' => 'required|string|email|max:255|unique:penitip,email',
                    'foto_ktp' => 'required|image|max:2048',
                    'no_ktp' => 'required|string|unique:penitip,no_ktp',
                    'password' => 'required|string',
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.max' => 'Email must not exceed 255 characters',
                    'foto_ktp.max' => 'Picture must not exceed 2 mb',
                    'foto_ktp.image' => 'Picture must be an image',
                    'foto_ktp.mimes' => 'Picture must be a file of type: jpeg, png, jpg, gif, svg',
                    'nama.required' => 'Name is required',
                    'telepon.required' => 'Phone number is required',
                    'no_ktp.required' => 'KTP number is required',
                    'no_ktp.unique' => 'KTP number must be unique',
                ]
            );

            if ($request->hasFile('foto_ktp')) {
                $foto = $request->file('foto_ktp');
                $fotoPath = $foto->store('images/penitip', 'public');
            }
            $penitip = Penitip::create([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'no_ktp' => $request->no_ktp,
                'foto_ktp' => isset($fotoPath) ? $fotoPath : null,
            ]);
            return response()->json([
                'message' => 'Data added successfully',
                'penitip' => $penitip,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function fetchPenitipByLogin(Request $request)
    {
        try {
            $penitip = Auth::guard('penitip')->user();
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

    public function fetchPenitip(Request $request)
    {
        try {
            $penitip = Penitip::all();;
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
    public function updatePenitip(Request $request, $id_penitip)
    {
        try {
            $penitip = Penitip::findOrFail($id_penitip);

            $request->validate(
                [
                    'nama' => 'required|string|max:255',
                    'telepon' => 'required|string',
                    'email' => 'required|string|email|max:255|unique:penitip,email,' . $id_penitip . ',id_penitip',
                    'foto_ktp' => 'nullable|image|max:2048',
                    'no_ktp' => 'required|string|unique:penitip,no_ktp,' . $id_penitip . ',id_penitip',
                    'password' => 'nullable|string',
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.max' => 'Email must not exceed 255 characters',
                    'foto_ktp.max' => 'Picture must not exceed 2 mb',
                    'foto_ktp.image' => 'Picture must be an image',
                    'foto_ktp.mimes' => 'Picture must be a file of type: jpeg, png, jpg, gif, svg',
                    'nama.required' => 'Name is required',
                    'telepon.required' => 'Phone number is required',
                    'no_ktp.required' => 'KTP number is required',
                    'no_ktp.unique' => 'KTP number must be unique',
                ]
            );

            if ($request->password == NULL) {
                $request->password = $penitip->password;
            }
            $cekEmail = Penitip::where('email', $request->email)->where('id_penitip', '!=', $id_penitip)->exists() ||
                DB::table('pembeli')->where('email', $request->email)->exists() ||
                DB::table('organisasi')->where('email', $request->email)->exists();

            if ($cekEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 400);
            }
            if ($request->hasFile('foto_ktp')) {
                if ($penitip->foto_ktp) {
                    Storage::disk('public')->delete($penitip->foto_ktp);
                }
                $foto = $request->file('foto_ktp');
                $fotoPath = $foto->store('images/penitip', 'public');
            }

            $penitip->update([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'no_ktp' => $request->no_ktp,
                'foto_ktp' => isset($fotoPath) ? $fotoPath : $penitip->foto_ktp,
            ]);
            return response()->json([
                'message' => 'Data updated successfully',
                'penitip' => $penitip,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePenitip($id_penitip)
    {
        try {
            $penitip = Penitip::findOrFail($id_penitip);
            if ($penitip->foto_ktp) {
                Storage::disk('public')->delete($penitip->foto_ktp);
            }
            $penitip->delete();
            return response()->json([
                'message' => 'Data deleted successfully',
            ], 200);
        } catch (Exception $e) {
            \Log::error("Failed to delete penitip: " . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}