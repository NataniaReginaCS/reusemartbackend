<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diskusi extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "diskusi";
    protected $primaryKey = 'id_diskusi';

    protected $fillable = [
        'id_pembeli',
        'id_pegawai',
        'id_barang',
        'pesan',
        'tanggal_diskusi',
    ];

    public function diskusiPembeli(): BelongsTo
    {
        return $this->belongsTo(Pembeli::class, 'id_pembeli');
    }

    public function diskusiBarang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function diskusiPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }

}
