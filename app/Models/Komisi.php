<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komisi extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "komisi";
    protected $primaryKey = 'id_komisi';

    protected $fillable = [
        'id_barang',
        'id_penitip',
        'id_pegawai',
        'komisi_reusemart',
        'komisi_penitip',
        'komisi_hunter',
        'bonus_penitip',
    ];

    public function komisiPenitip(): BelongsTo
    {
        return $this->belongsTo(Penitip::class, 'id_penitip');
    }

    public function komisiBarang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function komisiPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

}
