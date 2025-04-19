<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Organisasi extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    public $timestamps = false;
    protected $table = "organisasi";
    protected $primaryKey = 'id_organisasi';

    protected $fillable = [
        'nama',
        'alamat',
        'email',
        'PASSWORD',
        'foto',
    ];

    public function organisasiReqDonasi(): HasMany
    {
        return $this->hasMany(Requset_donasi::class, 'id_organisasi');
    }


}
