<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ADMIN = 'admin';
    public const MAHASISWA = 'mahasiswa';
    public const PEMBIMBING = 'pembimbing';

    public const PERAN = [
        self::MAHASISWA => 'Mahasiswa',
        self::PEMBIMBING => 'Dosen pembimbing',
        self::ADMIN => 'Admin',
    ];

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nomor_induk', 'no_hp', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN;
    }

    public function isMahasiswa(): bool
    {
        return $this->role === self::MAHASISWA;
    }

    public function isPembimbing(): bool
    {
        return $this->role === self::PEMBIMBING;
    }

    public function getPeranLabelAttribute(): string
    {
        return self::PERAN[$this->role] ?? ucfirst((string) $this->role);
    }

    /** Semua pengajuan KPI milik mahasiswa ini. */
    public function pengajuanKpi(): HasMany
    {
        return $this->hasMany(PengajuanKpi::class, 'mahasiswa_id');
    }

    /** Pengajuan paling baru milik mahasiswa ini (apa pun statusnya). */
    public function pengajuanTerakhir(): HasOne
    {
        return $this->hasOne(PengajuanKpi::class, 'mahasiswa_id')->latestOfMany();
    }

    /** Pengajuan KPI yang dibimbing oleh dosen ini. */
    public function mahasiswaBimbingan(): HasMany
    {
        return $this->hasMany(PengajuanKpi::class, 'pembimbing_id');
    }

    /** Pengajuan yang masih menunggu atau sedang berjalan (maksimal satu). */
    public function pengajuanAktif(): ?PengajuanKpi
    {
        return $this->pengajuanKpi()
            ->whereIn('status', [PengajuanKpi::DIAJUKAN, PengajuanKpi::BERJALAN])
            ->latest()
            ->first();
    }
}
