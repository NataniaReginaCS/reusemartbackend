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
    ];

    public function barangPenitipan(): BelongsTo
    {
        return $this->belongsTo(Penitipan::class, 'id_penitipan');
    }

    public function barangKategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function barangAlamat(): HasMany
    {
        return $this->HasMany(Alamat::class, 'id_pembeli');
    }

    public function detailKeranjang()
    {
        return $this->hasMany(Detail_keranjang::class, 'id_barang');
    }
    // Barang.php
    public function getFotoUrlAttribute()
    {
        return asset('storage/' . $this->foto);
    }

}
