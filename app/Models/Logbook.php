<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Logbook extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DISETUJUI = 'disetujui';
    public const REVISI = 'revisi';

    protected $fillable = [
        'pengajuan_kpi_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'kegiatan', 'lampiran', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKpi::class, 'pengajuan_kpi_id');
    }

    public function komentar(): MorphMany
    {
        return $this->morphMany(Komentar::class, 'commentable')->oldest();
    }

    public function getJamAttribute(): string
    {
        return substr((string) $this->jam_mulai, 0, 5).'–'.substr((string) $this->jam_selesai, 0, 5);
    }
}
