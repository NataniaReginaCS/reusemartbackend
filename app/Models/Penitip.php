<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Penitip extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    public $timestamps = false;
    protected $table = "penitip";
    protected $primaryKey = 'id_penitip';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'telepon',
        'wallet',
        'poin',
        'foto_ktp',
        'no_ktp',
        'badges',
        'total_rating',
        'fcm_token',
    ];

    public function getRoleAttribute()
    {
        return 'Penitip';
    }

    public function penitipKomisi(): HasMany
    {
        return $this->hasMany(Komisi::class, 'id_penitip');
    }

    public function penitipDiskusi(): BelongsTo 
    {
        return $this->belongsTo(Penitipan::class, 'id_penitip');
    }

}
