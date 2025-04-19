<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keranjang extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "keranjang";
    protected $primaryKey = 'id_keranjang';

    protected $fillable = [
        'id_pembeli',
    ];

    public function keranjangPembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }

    public function keranjangPembelian(): HasMany
    {
        return $this->belongsTo(HasMany::class, 'id_keranjang');
    }

}
