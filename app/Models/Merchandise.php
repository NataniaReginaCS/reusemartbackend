<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Merchandise extends Model
{
    protected $primaryKey = 'id_merchandise';
    protected $table = 'merchandise';
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'nama_merchandise',
        'deskripsi',
        'poin',
        'stok'
    ];

    public function merchandisePnPoin(): HasMany
    {
        return $this->hasMany(Penukaran_poin::class, 'id_merchandise');
    }


}
