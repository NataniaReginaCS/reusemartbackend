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
        'id_keranjang',
        'id_pegawai',
        'tanggal_laku',
        'tanggal_lunas',
        'tanggal_pengiriman',
        'ongkir',
        'status_pengiriman',
        'bukti_pembayaran',
        'poin_digunakan',
        'poin_didapat',
        'metode_pengiriman',
        'total',
        'nomor_nota',
    ];

    public function pembelianKeranjang(): BelongsTo
    {
        return $this->hasMany(BelongsTo::class, 'id_pembelian');
    }

    public function pembelianPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
