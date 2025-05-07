<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembeli extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    public $timestamps = false;
    protected $table = "pembeli";
    protected $primaryKey = 'id_pembeli';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'telepon',
        'poin',
        'foto',
    ];

    public function getRoleAttribute() {
        return 'Pembeli';
    }

    public function pembeliDiskusi(): HasMany
    {
        return $this->hasMany(Diskusi::class, 'id_pembeli');
    }

    public function pembeliAlamat(): HasMany
    {
        return $this->hasMany(Detail_donasi::class, 'id_pembeli');
    }

    public function pembeliKeranjang(): HasMany
    {
        return $this->hasMany(Keranjang::class, 'id_pembeli');
    }
}
