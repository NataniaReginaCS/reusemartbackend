<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Alamat extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "alamat";
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_pembeli',
        'nama_alamat',
        'nama_jalan',
        'nama_kota',
        'kode_pos',
        'isUtama',
    ];

    public function alamatPembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }
}
