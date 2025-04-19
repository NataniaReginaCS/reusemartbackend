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

    public function rolePegawai(): BelongsTo
    {
        return $this->BelongsTo(Organisasi::class, 'id_organisasi');
    }

}
