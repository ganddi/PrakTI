<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/** SDD Tabel 3.1: akun login seluruh pengguna. Data detail ada di Mahasiswa / DosenPembimbing. */
class User extends Authenticatable
{
    use Notifiable;

    public const ADMIN = 'admin';
    public const MAHASISWA = 'mahasiswa';
    public const PEMBIMBING = 'pembimbing';

    public const PERAN = [
        self::MAHASISWA => 'Mahasiswa',
        self::PEMBIMBING => 'Dosen pembimbing',
        self::ADMIN => 'Admin',
    ];

    public const AKTIF = 'aktif';
    public const NONAKTIF = 'nonaktif';
    public const MENUNGGU = 'menunggu';

    protected $fillable = ['username', 'email', 'password', 'role', 'status', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'last_login_at' => 'datetime'];
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(DosenPembimbing::class);
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class)->latest('id');
    }

    public function isAdmin(): bool { return $this->role === self::ADMIN; }
    public function isMahasiswa(): bool { return $this->role === self::MAHASISWA; }
    public function isPembimbing(): bool { return $this->role === self::PEMBIMBING; }

    /** Profil detail sesuai peran. */
    public function profil(): Mahasiswa|DosenPembimbing|null
    {
        return match ($this->role) {
            self::MAHASISWA => $this->mahasiswa,
            self::PEMBIMBING => $this->dosen,
            default => null,
        };
    }

    public function getNameAttribute(): string
    {
        return $this->profil()?->nama_lengkap ?? $this->username;
    }

    public function getNomorIndukAttribute(): ?string
    {
        return $this->mahasiswa?->nim ?? $this->dosen?->nidn;
    }

    public function getNoHpAttribute(): ?string
    {
        return $this->profil()?->no_hp;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::AKTIF;
    }

    public function getPeranLabelAttribute(): string
    {
        return self::PERAN[$this->role] ?? ucfirst((string) $this->role);
    }
}
