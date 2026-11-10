<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Progress extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DIVERIFIKASI = 'diverifikasi';
    public const REVISI = 'revisi';

    protected $table = 'progress';

    protected $fillable = ['pengajuan_kpi_id', 'judul', 'deskripsi', 'persentase', 'lampiran', 'status'];

    protected function casts(): array
    {
        return ['persentase' => 'integer'];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanKpi::class, 'pengajuan_kpi_id');
    }

    public function komentar(): MorphMany
    {
        return $this->morphMany(Komentar::class, 'commentable')->oldest();
    }
}
