<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barang extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "barang";
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'id_penitipan',
        'id_kategori',
        'id_hunter',
        'nama',
        'deskripsi',
        'foto',
        'berat',
        'isGaransi',
        'akhir_garansi',
        'status_perpanjangan',
        'harga',
        'tanggal_akhir',
        'batas_ambil',
        'status_barang',
        'tanggal_ambil',
        'durasi_penitipan',
    ];

    public function barangPenitipan(): BelongsTo
    {
        return $this->belongsTo(Penitipan::class, 'id_penitipan', 'id_penitipan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }


    public function barangKategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function barangAlamat(): HasMany
    {
        return $this->HasMany(Alamat::class, 'id_pembeli');
    }

    public function detailpem()
    {
        return $this->hasMany(Detail_pembelian::class, 'id_barang', 'id_barang');
    }

    public function penitip(): BelongsTo
    {
        return $this->barangPenitipan->penitipanPenitip();
    }

    // Barang.php
    public function getFotoUrlAttribute()
    {
        return asset('storage/' . $this->foto);
    }

}
