<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function addPegawai(Request $request)
    {
        try {
            $request->validate(
                [
                    'id_role' => 'required',
                    'nama' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:pegawai,email',
                    'password' => 'required|string|min:8',
                    'tanggal_masuk' => 'required|date',
                    'tanggal_lahir' => ['required', 'date', 'before:tanggal_masuk', 'before:today'],
                    'wallet' => 'required',
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.max' => 'Email must not exceed 255 characters',
                    'password.required' => 'Password is required',
                    'password.min' => 'Password must be at least 8 characters',
                    'tanggal_masuk.required' => 'Tanggal Masuk is required',
                    'tanggal_lahir.required' => 'Tanggal Lahir is required',
                    'nama.required' => 'Name is required',
                    'id_role.required' => 'Role ID is required',
                ]
            );

            $pegawai = Pegawai::create([
                'id_role' => $request->id_role,
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tanggal_masuk' => $request->tanggal_masuk,
                'tanggal_lahir' => $request->tanggal_lahir,
                'wallet' => $request->wallet,
            ]);
            return response()->json([
                'message' => 'Data added successfully',
                'pegawai' => $pegawai,
            ], 201);

        
            $cekEmail = Pegawai::where('email', $request->email)->where('id_pegawai', '!=', $pegawaiId)->exists();
            if ($cekEmail) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already exists',
                ], 400);
            }
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

    public function index()
    {
        try{
            $pegawai = Pegawai::all();
            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $pegawai
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $pegawai = Pegawai::find($id);
        if (!$pegawai) {
            return response()->json([
                'status' => false,
                'message' => 'Pegawai not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Pegawai retrieved successfully',
            'data' => $pegawai
        ], 200);
    }

        public function updatePegawai(Request $request, $pegawaiId)
        {
            try{
                $pegawai = Pegawai::findOrFail($pegawaiId);
        
                $validatedData = $request->validate([
                    'id_role' => 'sometimes|exists:role,id_role',
                    'nama' => 'sometimes|string|max:255',
                    'email' => 'sometimes|email|max:255|unique:pegawai,email,' . $pegawai->id_pegawai . ',id_pegawai',
                    'password' => 'nullable|string|min:8',
                    'tanggal_masuk' => 'required|date',
                    'tanggal_lahir' => ['required', 'date', 'before:tanggal_masuk', 'before:today'],
                    'wallet' => 'sometimes',
                ],[
                    'email.unique' => 'Email already exists',
                    'password.min' => 'Password must be at least 8 characters',
                    'tanggal_masuk.date' => 'Invalid date format for Tanggal Masuk',

                ]);

                $passwordInput = $request->input('password');
                if ($passwordInput !== null && $passwordInput !== '') {
                    $validatedData['password'] = Hash::make($passwordInput);
                } else {
                    unset($validatedData['password']);
                }

                $cekEmail = Pegawai::where('email', $request->email)->where('id_pegawai', '!=', $pegawaiId)->exists();
                if ($cekEmail) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email already exists',
                    ], 400);
                }
                $pegawai->update($validatedData);

                return response()->json([
                    "status" => true,
                    "message" => "Pegawai updated successfully",
                    "data" => $pegawai
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pegawai not found',
                    'error' => $e->getMessage(),
                ], 404);
            }
        }

        public function deletePegawai($id)
        {
            try{
                $pegawai = Pegawai::findOrFail($id);
                $pegawai->delete();
                
                return response()->json([
                    "status" => true,
                    "message" => "Pegawai berhasil dihapus.",
                    "data" => null
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pegawai not found',
                    'error' => $e->getMessage(),
                ], 404);
            }
        }



    public function searchPegawai(Request $request)
    {
        try {
            $query = $request->input('query');
            $pegawai = Pegawai::where('nama', 'LIKE', "%$query%")
                ->orWhere('email', 'LIKE', "%$query%")
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $pegawai
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function resetPasswordPegawai($id)
    {
        try{
            $pegawai = Pegawai::findOrFail($id);
            $pegawai->password = Hash::make($pegawai->tanggal_lahir);
            $pegawai->save();
            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully', 
                'pegawai' => $pegawai,
                
            ], 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
