<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;
    public $timestamps = false;
    protected $table = "pegawai";
    protected $primaryKey = 'id_pegawai';

    protected $fillable = [
        'id_role',
        'nama',
        'email',
        'PASSWORD',
        'tanggal_masuk',
        'tanggal_lahir',
        'wallet',
    ];

    public function pegawaiDiskusi(): HasMany
    {
        return $this->hasMany(Diskusi::class, 'id_pegawai');
    }

    public function pegawaiDtDonasi(): HasMany
    {
        return $this->hasMany(Detail_donasi::class, 'id_pegawai');
    }

    public function pegawaiKomisi(): HasMany
    {
        return $this->hasMany(Komisi::class, 'id_pegawai');
    }

    public function pegawaiRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function pegawaiPembelian(): HasMany
    {
        return $this->hasMany(Pembelian::class, 'id_pegawai');
    }
}
