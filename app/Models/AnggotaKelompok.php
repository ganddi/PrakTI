<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.7 */
class AnggotaKelompok extends Model
{
    protected $table = 'anggota_kelompok';

    protected $fillable = ['kelompok_id', 'mahasiswa_id', 'tanggal_masuk', 'status'];

    protected function casts(): array
    {
        return ['kelompok_id' => 'integer', 'mahasiswa_id' => 'integer', 'tanggal_masuk' => 'date'];
    }

    public function kelompok(): BelongsTo { return $this->belongsTo(KelompokKpi::class, 'kelompok_id'); }
    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }
}
