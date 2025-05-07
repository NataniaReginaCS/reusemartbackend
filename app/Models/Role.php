<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table ="role";
    protected $primaryKey = 'id_role';

    protected $fillable = [
        'nama_role',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'id_role');
    }

}
