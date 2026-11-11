<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** SDD Tabel 3.2 */
class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = ['user_id', 'nim', 'nama_lengkap', 'email', 'no_hp', 'program_studi', 'fakultas', 'alamat'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function pengajuanKpi(): HasMany { return $this->hasMany(PengajuanKpi::class); }
    public function pengajuanTerakhir(): HasOne { return $this->hasOne(PengajuanKpi::class)->latestOfMany(); }
    public function anggota(): HasMany { return $this->hasMany(AnggotaKelompok::class); }
    public function logbook(): HasMany { return $this->hasMany(Logbook::class)->orderByDesc('tanggal'); }
    public function laporan(): HasMany { return $this->hasMany(LaporanKpi::class)->latest('id'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenKpi::class)->latest('id'); }
    public function penilaian(): HasMany { return $this->hasMany(Penilaian::class); }
    public function nilaiAkhir(): HasOne { return $this->hasOne(NilaiAkhir::class); }

    public function getNameAttribute(): string { return $this->nama_lengkap; }
    public function getNomorIndukAttribute(): string { return $this->nim; }
}
