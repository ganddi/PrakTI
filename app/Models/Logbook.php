<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.11 */
class Logbook extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DISETUJUI = 'disetujui';
    public const REVISI = 'revisi';

    protected $table = 'logbook';

    protected $fillable = ['mahasiswa_id', 'tanggal', 'judul_kegiatan', 'deskripsi_kegiatan', 'hasil_kegiatan', 'kendala', 'status', 'catatan_dosen'];

    protected function casts(): array
    {
        return ['mahasiswa_id' => 'integer', 'tanggal' => 'date'];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }
}
