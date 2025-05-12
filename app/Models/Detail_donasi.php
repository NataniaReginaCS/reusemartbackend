<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detail_donasi extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "detail_donasi";
    protected $primaryKey = 'id_detaildonasi';

    protected $fillable = [
        'id_request',
        'id_pegawai',
        'id_barang',
        'tanggal_donasi',
        'nama_penerima',
        'reward_sosial'
    ];

    public function dtDonasiReqDonasi(): BelongsTo
    {
        return $this->belongsTo(Request_donasi::class, 'id_request');
    }

    public function dtDonasiBarang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function dtDonasiPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
