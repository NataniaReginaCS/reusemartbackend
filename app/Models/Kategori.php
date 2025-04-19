<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kategori extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "kategori";
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama',
    ];

    public function kategoriBarang(): HasMany
    {
        return $this->hasMany(Barang::class, 'id_kategori');
    }

}
