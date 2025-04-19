<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detail_keranjang extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "detail_keranjang";
    protected $primaryKey = 'id_detail_keranjang';

    protected $fillable = [
        'id_keranjang',
        'id_barang',
    ];

    public function dtKeranjangKeranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class, 'id_keranjang');
    }

    public function dtKeranjangBarang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

}
