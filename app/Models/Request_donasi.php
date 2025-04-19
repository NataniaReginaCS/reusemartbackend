<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request_donasi extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = "request_donasi";
    protected $primaryKey = 'id_request';
    protected $fillable = [
        'id_organisasi',
        'tanggal_request',
        'tahun',
        'status_terpenuhi',
        'deskripsi',
    ];

    public function reqDonasiOrganisasi(): BelongsTo
    {
        return $this->BelongsTo(Organisasi::class, 'id_organisasi');
    }

    public function reqDonasiDtDonasi(): HasMany
    {
        return $this->HasMany(Detail_donasi::class, 'id_request');
    }
}
