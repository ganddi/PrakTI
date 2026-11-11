<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** SDD Tabel 3.4. Status: aktif (tampil di landing page), nonaktif, usulan (diusulkan mahasiswa). */
class TempatKpi extends Model
{
    public const AKTIF = 'aktif';
    public const NONAKTIF = 'nonaktif';
    public const USULAN = 'usulan';

    protected $table = 'tempat_kpi';

    protected $fillable = [
        'nama_instansi', 'jenis_instansi', 'alamat', 'kota', 'provinsi',
        'nama_pic', 'email_pic', 'no_hp_pic', 'deskripsi', 'status',
    ];

    public function pengajuan(): HasMany { return $this->hasMany(PengajuanKpi::class); }
    public function penempatan(): HasMany { return $this->hasMany(PenempatanDosen::class); }

    public function getAlamatLengkapAttribute(): string
    {
        return trim($this->alamat.', '.$this->kota.', '.$this->provinsi, ', ');
    }
}
