<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembelian extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "pembelian";
    protected $primaryKey = 'id_pembelian';

    protected $fillable = [
        'id_pegawai',
        'id_alamat',
        'id_pembeli',
        'tanggal_laku',
        'tanggal_lunas',
        'tanggal_pengiriman',
        'ongkir',
        'status_pengiriman',
        'status_pembayaran',
        'bukti_pembayaran',
        'poin_digunakan',
        'poin_didapat',
        'metode_pengiriman',
        'total',
        'nomor_nota',
    ];

    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class, 'id_keranjang');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'id_alamat');
    }
    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }
}

