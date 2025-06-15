<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penitipan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "penitipan";
    protected $primaryKey = 'id_penitipan';

    protected $fillable = [
        'id_penitip',
        'id_pegawai',
        'tanggal_masuk',
    ];

    public function penitipanPegawai(): BelongsTo
    {
        return $this->BelongsTo(Pegawai::class, 'id_pegawai');
    }

    public function penitipanPenitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'id_penitip', 'id_penitip');
    }

    public function penitipanBarang(): HasMany
    {
        return $this->hasMany(Barang::class, 'id_penitipan', 'id_penitipan');
    }

}
