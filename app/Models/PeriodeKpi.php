<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** SDD Tabel 3.5 */
class PeriodeKpi extends Model
{
    public const AKTIF = 'aktif';
    public const SELESAI = 'selesai';

    protected $table = 'periode_kpi';

    protected $fillable = ['kode_periode', 'nama_periode', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'keterangan', 'status'];

    protected function casts(): array
    {
        return ['tahun' => 'integer', 'tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function kelompok(): HasMany { return $this->hasMany(KelompokKpi::class, 'periode_id')->orderBy('kode_kelompok'); }
    public function pengajuan(): HasMany { return $this->hasMany(PengajuanKpi::class, 'periode_id'); }
}
