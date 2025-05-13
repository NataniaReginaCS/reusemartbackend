<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diskusi;
use App\Models\Pembeli;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DiskusiController extends Controller
{
    public function fetchDiskusi($idBarang){
        try{

            $diskusis = Diskusi::where('id_barang', $idBarang)->orderBy('tanggal_diskusi', 'asc')->get();

                if ($diskusis->isEmpty()) {
                return response()->json([
                    'message' => 'No diskusi found',
                ], 404);
            }

            $data = [];

            foreach ($diskusis as $diskusi) {
                if ($diskusi->id_pembeli != null) {
                    $pembeli = Pembeli::find($diskusi->id_pembeli);
                    $nama = $pembeli?->nama ?? 'Pembeli Tidak Ditemukan';
                    $foto = $pembeli?->foto ?? null;
                    $role = 'Pembeli';
                } else if ($diskusi->id_pegawai != null) {
                    $pegawai = Pegawai::find($diskusi->id_pegawai);
                    $nama = $pegawai?->nama ?? 'Pegawai Tidak Ditemukan';
                    $foto = asset('storage/images/CS.jpeg');
                    $role = 'CS';
                }

                $data[] = [
                    'pesan' => $diskusi->pesan,
                    'nama' => $nama,
                    'foto' => $foto,
                    'tanggal' => $diskusi->tanggal_diskusi,
                    'role'  => $role,
                ];
            }

            return response()->json([
                'message' => 'Diskusi fetched successfully',
                'diskusi' => $data,
            ], 200);

        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Error fetching diskusi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchDiskusiCS(){
        try{
                $diskusi = DB::table('diskusi')
                ->join('pembeli', 'diskusi.id_pembeli', '=', 'pembeli.id_pembeli')
                ->where('diskusi.id_pegawai', null)
                ->select(
                    'pembeli.nama as nama_pembeli',
                    'pembeli.foto as foto_pembeli',
                    'diskusi.pesan',
                    'diskusi.tanggal_diskusi',
                    'diskusi.id_barang',
                    'diskusi.id_diskusi'
                )->orderBy('tanggal_diskusi', 'desc')->get();

            if ($diskusi->isEmpty()) {
                return response()->json([
                    'message' => 'No diskusi found',
                ], 404);
            }

            return response()->json([
                'message' => 'Diskusi fetched successfully',
                'diskusi' => $diskusi,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error fetching diskusi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function addDiskusi(Request $request, $idBarang){
        try{
            $pesan = $request->getContent(); 
            $pesan = trim($pesan, '"');
            if (!$pesan || strlen($pesan) > 255) {
                return response()->json(['message' => 'Pesan tidak valid'], 422);
            }
            $pembeli = Auth::guard('pembeli')->user();
            if($pembeli){
                $diskusi = Diskusi::create([
                    'id_barang' => $idBarang,
                    'id_pembeli' => $pembeli->id_pembeli,
                    'id_pegawai' => null,
                    'pesan' => $pesan,
                    'tanggal_diskusi' => Carbon::now(),
                ]);
            }else{
                $pegawai = Auth::guard('pegawai')->user();
                if($pegawai){
                    $diskusi = Diskusi::create([
                        'id_barang' => $idBarang,
                        'id_pembeli' => null,
                        'id_pegawai' => $pegawai->id_pegawai,
                        'pesan' => $pesan,
                        'tanggal_diskusi' => Carbon::now(),
                    ]);
                }else{
                    return response()->json([
                        'message' => 'User not authenticated',
                    ], 401);
                }
            }
            return response()->json([
                'message' => 'Diskusi created successfully',
                'diskusi' => $diskusi,
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error creating diskusi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}