<?php

namespace App\Models;

use App\Support\Berkas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.10 */
class DokumenKpi extends Model
{
    public const MENUNGGU = 'menunggu';
    public const DIVERIFIKASI = 'diverifikasi';
    public const REVISI = 'revisi';
    public const TERSIMPAN = 'tersimpan';

    public const JENIS = [
        'persyaratan' => 'Dokumen persyaratan',
        'proposal' => 'Proposal KPI',
        'surat_balasan' => 'Surat balasan instansi',
        'logbook' => 'Logbook bertanda tangan',
        'penilaian_penguji' => 'Dokumen penilaian dosen penguji',
        'penilaian_lapangan' => 'Dokumen penilaian pembimbing lapangan',
        'nilai_akhir' => 'Dokumen nilai akhir bertanda tangan',
        'lainnya' => 'Dokumen lainnya',
    ];

    /** Jenis yang diperiksa admin. Jenis lain berstatus "tersimpan". */
    public const PERLU_VERIFIKASI = ['surat_balasan', 'penilaian_penguji', 'penilaian_lapangan', 'nilai_akhir'];

    protected $table = 'dokumen_kpi';

    protected $fillable = [
        'mahasiswa_id', 'pengajuan_id', 'jenis_dokumen', 'nama_file', 'format_file', 'ukuran_file', 'path_file',
        'status_verifikasi', 'catatan', 'uploaded_at', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime', 'verified_at' => 'datetime', 'ukuran_file' => 'integer'];
    }

    public function mahasiswa(): BelongsTo { return $this->belongsTo(Mahasiswa::class); }
    public function pengajuan(): BelongsTo { return $this->belongsTo(PengajuanKpi::class, 'pengajuan_id'); }

    public function getJenisLabelAttribute(): string { return self::JENIS[$this->jenis_dokumen] ?? $this->jenis_dokumen; }
    public function getUkuranFormatAttribute(): string { return Berkas::ukuran($this->ukuran_file); }
}
