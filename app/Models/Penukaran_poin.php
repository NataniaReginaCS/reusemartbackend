<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penukaran_poin extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "penukaran_poin";
    protected $primaryKey = 'id_penukaranpoin';

    protected $fillable = [
        'id_merchandise',
        'id_pembeli',
        'tanggal_penukaran',
        'status_verifikasi',
        'tanggal_ambil',
    ];

    public function penPoinDtDonasi(): HasMany
    {
        return $this->HasMany(Detail_donasi::class, 'id_penukaranpoin');
    }

    public function penPoinMerchandise(): BelongsTo
    {
        return $this->belongsTo(Merchandise::class, 'id_merchandise');
    }

    public function penPoinPembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }
}
