<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SDD Tabel 3.16. Untuk modul proses KPI (MODUL_KPI), referensi_id berisi mahasiswa_id
 * sehingga riwayat satu mahasiswa bisa ditelusuri.
 */
class AktivitasSistem extends Model
{
    public const UPDATED_AT = null;

    public const MODUL_KPI = ['pengajuan', 'surat', 'kelompok', 'penempatan', 'logbook', 'laporan', 'penilaian', 'nilai_akhir', 'dokumen'];

    protected $table = 'aktivitas_sistem';

    protected $fillable = ['user_id', 'aktivitas', 'modul', 'ip_address', 'user_agent', 'deskripsi', 'referensi_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'referensi_id' => 'integer'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Pencatatan gagal tidak menggagalkan proses utama, tetapi dilaporkan ke log (UC-25). */
    public static function catat(string $aktivitas, string $modul, ?int $referensiId = null, ?string $deskripsi = null, ?User $oleh = null): void
    {
        $userId = $oleh?->id ?? auth()->id();
        if (! $userId) {
            return;
        }

        try {
            static::create([
                'user_id' => $userId,
                'aktivitas' => $aktivitas,
                'modul' => $modul,
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
                'user_agent' => app()->runningInConsole() ? null : substr((string) request()->userAgent(), 0, 500),
                'deskripsi' => $deskripsi,
                'referensi_id' => $referensiId,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
