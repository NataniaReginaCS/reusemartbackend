<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Illuminate\Http\Request;
use App\Models\Pegawai;
use App\Models\Detail_pembelian;
use App\Models\Pembeli;
use App\Models\Barang;
use App\Models\Penitip;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    public function fetchPegawaiByLogin(Request $request)
    {
        try {
            $pegawai = Auth::guard('pegawai')->user();
            return response()->json([
                'pegawai' => $pegawai,
                'message' => 'Data retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJadwalPengirimanKurir(){
        $kurir = Auth::guard('pegawai')->user();
        try {
            $jadwalPengiriman = DB::table('pembelian')
                ->where('pembelian.metode_pengiriman', 'diantar')
                ->where('pembelian.status_pengiriman',   '!=', 'Selesai')
                ->where('pembelian.status_pembayaran', '!=', 'batal')
                ->where('pembelian.id_pegawai', $kurir->id_pegawai)
                ->join('pembeli', 'pembelian.id_pembeli', '=', 'pembeli.id_pembeli')
                ->join('alamat', 'pembelian.id_alamat', '=', 'alamat.id_alamat')
                ->select(
                    'pembelian.id_pembelian as id_pembelian',
                    'pembelian.tanggal_pengiriman as tanggal_pengiriman',
                    'pembelian.status_pengiriman as status_pengiriman',
                    'pembelian.metode_pengiriman as metode_pengiriman',
                    'pembeli.nama as nama_pembeli',
                    'alamat.nama_jalan as nama_jalan',
                )
                ->get();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'jadwal' => $jadwalPengiriman,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
        try {
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
        try {
            $pegawai = Pegawai::findOrFail($pegawaiId);

            $validatedData = $request->validate([
                'id_role' => 'sometimes|exists:role,id_role',
                'nama' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:pegawai,email,' . $pegawai->id_pegawai . ',id_pegawai',
                'password' => 'sometimes|string|min:8',
                'tanggal_masuk' => 'required|date',
                'tanggal_lahir' => ['required', 'date', 'before:tanggal_masuk', 'before:today'],
                'wallet' => 'sometimes',
            ], [
                'email.unique' => 'Email already exists',
                'password.min' => 'Password must be at least 8 characters',
                'tanggal_masuk.date' => 'Invalid date format for Tanggal Masuk',

            ]);

            if ($request->has('password') && $request->password !== null) {
                $validatedData['password'] = Hash::make($request->password);
            } else {
                $validatedData['password'] = $pegawai->password;
            }


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
        try {
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
        try {
            $pegawai = Pegawai::findOrFail($id);
            $pegawai->password = Hash::make($pegawai->tanggal_lahir);
            $pegawai->save();
            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully',
                'pegawai' => $pegawai,

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to reset password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchTransaksibyGudang()
    {
        try {
            $data = DB::table('pembelian')
                ->where(function ($query) {
                    $query->where('pembelian.metode_pengiriman', 'Diambil')
                        ->orWhere('pembelian.metode_pengiriman', 'Diantar');
                })
                ->select(
                    'pembelian.id_pembelian as id_pembelian',
                    'pembelian.status_pengiriman as status_pengiriman',
                    'pembelian.metode_pengiriman as metode_pengiriman',
                    'pembelian.tanggal_lunas',
                )
                ->get();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function fetchTransaksiGudangById($id_pembelian)
    {
        try {
            $data = DB::table('pembelian')
                ->join('detail_pembelian', 'pembelian.id_pembelian', '=', 'detail_pembelian.id_pembelian')
                ->join('barang', 'detail_pembelian.id_barang', '=', 'barang.id_barang')
                ->join('penitipan', 'barang.id_penitipan', '=', 'penitipan.id_penitipan')
                ->join('penitip', 'penitipan.id_penitip', '=', 'penitip.id_penitip')
                ->where('pembelian.id_pembelian', $id_pembelian)
                ->select(
                    'barang.id_barang as id_barang',
                    'pembelian.id_pembelian as id_pembelian',
                    'pembelian.status_pengiriman as status_pengiriman',
                    'pembelian.metode_pengiriman as metode_pengiriman',
                    'pembelian.tanggal_lunas',
                    'penitip.nama as nama_penitip',
                    'barang.nama as nama_barang',
                    'barang.status_barang as status_barang',
                    'barang.harga as harga',
                    'barang.foto as foto_barang',
                    'barang.status_barang as status_barang'
                )
                ->get();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchDataPembelian($id_pembelian)
    {
        try {
            $data = DB::table('pembelian')
                ->where('pembelian.id_pembelian', $id_pembelian)
                ->select(
                    'pembelian.id_pembelian as id_pembelian',
                    'pembelian.status_pengiriman as status_pengiriman',
                    'pembelian.metode_pengiriman as metode_pengiriman',
                    'pembelian.tanggal_lunas',
                )
                ->first();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function fetchDataPegawai()
    {
        try {
            $data = DB::table('pegawai')
                ->where('pegawai.id_role', '3')

                ->select(
                    'pegawai.id_pegawai as id_pegawai',
                    'pegawai.nama as nama',


                )
                ->get();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'id_pegawai' => 'sometimes|required|integer|exists:pegawai,id_pegawai',
            'tanggal_pengiriman' => 'sometimes|required|date',

        ]);

        try {
            $pembelian = Pembelian::find($id);
            if ($pembelian) {
                $pembelian->update($validatedData);

                $barang = Detail_pembelian::where('id_pembelian', $pembelian->id_pembelian)->get();
                $pembeli = Pembeli::where('id_pembeli', $pembelian->id_pembeli)->first();
            
                if($pembelian->metode_pengiriman == 'diantar'){
                    $kurir = Pegawai::where('id_pegawai', $pembelian->id_pegawai)->first();
                    if($kurir) {
                        $this->sendNotificationToKurir($kurir, 'Penjadwalan Barang', "Anda telah dijadwalkan untuk mengirim barang pada tanggal {$pembelian->tanggal_pengiriman}.Silahkan datang ke gudang untuk mengambil barang yang akan dikirim.");
                    }
                }
                if ($pembeli) {
                    if($pembelian->metode_pengiriman == 'diambil'){
                        $this->sendNotificationToPembeli($pembeli, 'Penjadwalan Barang', "Transaksi dengan nomor nota {$pembelian->nomor_nota} telah dijadwalkan untuk diambil pada tanggal {$pembelian->tanggal_pengiriman}. Silahkan datang ke gudang untuk mengambil barang yang telah dibeli.");
                    }
                    $this->sendNotificationToPembeli($pembeli, 'Penjadwalan Barang', "Transaksi dengan nomor nota {$pembelian->nomor_nota} telah dijadwalkan untuk dikirim pada tanggal {$pembelian->tanggal_pengiriman}. Terima kasih telah menggunakan layanan kami.");
                }

                foreach ($barang as $item) {
                    $itemBarang = Barang::with('barangPenitipan.penitipanPenitip')->where('id_barang', $item->id_barang)->first();
                    if ($itemBarang) {
                        if($pembelian->metode_pengiriman == 'diambil'){
                            $this->sendNotificationToPenitip($itemBarang, 'Penjadwalan Barang', "Barang atas nama {$itemBarang->nama} telah dijadwalkan untuk diambil pembeli pada tanggal {$pembelian->tanggal_pengiriman}.Terima kasih telah menggunakan layanan kami.");
                        }else{

                            $this->sendNotificationToPenitip($itemBarang, 'Penjadwalan Barang', "Barang atas nama {$itemBarang->nama} telah dijadwalkan untuk dikirim pada tanggal {$pembelian->tanggal_pengiriman}.Terima kasih telah menggunakan layanan kami.");
                        }
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'pembelian updated successfully',
                    'data' => $pembelian
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Pembelian not found'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update pembelian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendNotificationToPenitip($barang, $title, $body)
    {
        $user = $barang->barangPenitipan?->penitipanPenitip;

        if ($user && $user->fcm_token) {
            $keyFile = config('firebase.projects.app.credentials.file');

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials($scopes, $keyFile);

            $token = $credentials->fetchAuthToken()['access_token'];

            $projectId = 'reusemart-a150d';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ]);

        
        } 
    }
    private function sendNotificationToPembeli($user, $title, $body)
    {
        
        if ($user && $user->fcm_token) {
            $keyFile = config('firebase.projects.app.credentials.file');

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials($scopes, $keyFile);

            $token = $credentials->fetchAuthToken()['access_token'];

            $projectId = 'reusemart-a150d';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ]);
        } 

        
        
    }

    private function sendNotificationToKurir($user, $title, $body)
    {
        if ($user && $user->fcm_token) {
            $keyFile = config('firebase.projects.app.credentials.file');

            $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

            $credentials = new ServiceAccountCredentials($scopes, $keyFile);

            $token = $credentials->fetchAuthToken()['access_token'];

            $projectId = 'reusemart-a150d';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ]);
        } 
    }
}
