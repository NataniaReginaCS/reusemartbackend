<?php

namespace App\Http\Controllers;

use App\Models\Pembeli;
use App\Models\Organisasi;
use App\Models\Keranjang;
use App\Models\Pegawai;

use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class AuthController extends Controller
{
    public function registerPembeli(Request $request)
    {
        try {

            $request->validate(
                [
                    'nama' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                    'password' => 'required|string',
                    'telepon' => 'required|string|unique:pembeli',
                    'foto' => 'nullable|string|max:255',
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.max' => 'Email must not exceed 255 characters',
                    'password.required' => 'Password is required',
                    'telepon.required' => 'Phone number is required',
                    'telepon.unique' => 'Phone number already exists',
                    'foto.max' => 'Picture must not exceed 2 mb',
                ]
            );

            $cekEmail = DB::table('pembeli')->where('email', $request->email)->exists() ||
                DB::table('organisasi')->where('email', $request->email)->exists() ||
                DB::table('penitip')->where('email', $request->email)->exists();
            if ($cekEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 400);
            }

            $fotoPath = $request->foto ? $request->foto : 'profile/default.png';

            $pembeli = Pembeli::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telepon' => $request->telepon,
                'poin' => 0,
                'foto' => $fotoPath,
            ]);

        

            return response()->json([
                'pembeli' => $pembeli,
                'message' => 'User  registered sucessfully',
            ], 201, [], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerOrganisasi(Request $request)
    {
        try {

            $request->validate(
                [
                    'nama' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                    'alamat' => 'required|string',
                    'telp' => 'required|string|unique:organisasi',
                    'password' => 'required|string|min:8',
                    'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ],
                [
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.max' => 'Email must not exceed 255 characters',
                    'password.required' => 'Password is required',
                    'telp.required' => 'Phone number is required',
                    'telp.unique' => 'Phone number already exists',
                    'foto.max' => 'Picture must not exceed 2 mb',
                ]
            );

            $cekEmail = DB::table('pembeli')->where('email', $request->email)->exists() ||
                DB::table('organisasi')->where('email', $request->email)->exists() ||
                DB::table('penitip')->where('email', $request->email)->exists();
            if ($cekEmail) {
                return response()->json([
                    'message' => 'Email already exists',
                ], 400);
            }


            $foto = $request->file('foto');
            $fotoPath = $foto->store('images/organisasi', 'public');
            $uploadFotoPath = basename($fotoPath);

            $organisasi = Organisasi::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'telp' => $request->telp,
                'password' => Hash::make($request->password),
                'foto' => $fotoPath,
            ]);


            return response()->json([
                'organisasi' => $organisasi,
                'message' => 'User  registered sucessfully',
            ], 201, [], JSON_UNESCAPED_SLASHES);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'error' => $e->validator->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $userTypes = [
            'pembeli' => Pembeli::class,
            'organisasi' => Organisasi::class,
            'penitip' => Penitip::class,
            'pegawai' => Pegawai::class,
        ];

        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        foreach ($userTypes as $userType => $model) {

            $user = $model::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $role = $user->role;
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user,
                    'role' => $role,
                    'token' => $token,
                ], 200);
            }
        }
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    public function cekRole(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return response()->json([
                'role' => $user->role,
            ], 200);    
        }
    }
}




