<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Detail_keranjang;
class Keranjang extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "keranjang";
    protected $primaryKey = 'id_keranjang';

    protected $fillable = [
        'id_pembeli',
    ];

    public function pembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }
    public function detailKeranjang()
    {
        return $this->hasMany(Detail_keranjang::class, 'id_keranjang');
    }
    public function pembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'id_keranjang');
    }
}
