<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "rating";
    protected $primaryKey = 'id_rating';
    protected $fillable = [
        'id_penitip',
        'id_pembeli',
        'id_barang',
        'rating_diberikan',
    ];

    public function ratingPenitip(): BelongsTo
    {
        return $this->BelongsTo(Penitip::class, 'id_penitip');
    }

    public function ratingPembeli(): BelongsTo
    {
        return $this->BelongsTo(Pembeli::class, 'id_pembeli');
    }

    public function ratingBarang(): BelongsTo
    {
        return $this->BelongsTo(Barang::class, 'id_barang');
    }
}
