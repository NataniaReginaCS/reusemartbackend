<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Detail_pembelian extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "detail_pembelian";
    protected $primaryKey = 'id_detail_pembelian';

    protected $fillable = [
        'id_pembelian',
        'id_barang',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}
