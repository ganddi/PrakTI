<?php

namespace App\Models;

use App\Notifications\PemberitahuanEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SDD Tabel 3.15: notifikasi di dalam aplikasi, juga dikirim lewat email bila tersedia. */
class Notifikasi extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'notifikasi';

    protected $fillable = ['user_id', 'judul', 'pesan', 'jenis', 'referensi_id', 'is_read', 'read_at'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean', 'read_at' => 'datetime', 'created_at' => 'datetime', 'referensi_id' => 'integer'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * Kirim notifikasi. $penerima boleh User, Mahasiswa, DosenPembimbing, atau kumpulan dari ketiganya.
     * Kegagalan email tidak menggagalkan proses (UC-25 exception flow).
     */
    public static function kirim($penerima, string $judul, string|array $pesan, string $jenis, ?int $referensiId = null): void
    {
        $pesan = is_array($pesan) ? implode(' ', array_filter($pesan)) : $pesan;

        $users = collect(is_iterable($penerima) ? $penerima : [$penerima])
            ->filter()
            ->map(fn ($p) => $p instanceof User ? $p : $p->user)
            ->filter()->unique('id');

        foreach ($users as $user) {
            $n = static::create(['user_id' => $user->id, 'judul' => $judul, 'pesan' => $pesan, 'jenis' => $jenis, 'referensi_id' => $referensiId, 'is_read' => false]);
            $n->setRelation('user', $user);

            try {
                $user->notify(new PemberitahuanEmail($judul, [$pesan], $n->tautan()));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** Halaman tujuan saat notifikasi dibuka, disesuaikan dengan peran penerima. */
    public function tautan(): string
    {
        $role = $this->user->role;
        $ref = $this->referensi_id;

        return match (true) {
            $role === User::ADMIN && $this->jenis === 'pengajuan' && $ref => route('admin.pengajuan.show', $ref),
            $role === User::ADMIN && $this->jenis === 'surat' => route('admin.verifikasi.index', ['tab' => 'surat']),
            $role === User::ADMIN && $this->jenis === 'penilaian' => route('admin.verifikasi.index', ['tab' => 'penilaian']),
            $role === User::ADMIN && $this->jenis === 'nilai_akhir' => route('admin.verifikasi.index', ['tab' => 'nilai_akhir']),
            $role === User::ADMIN && $this->jenis === 'penempatan' => route('admin.penempatan.index'),
            $role === User::ADMIN && $this->jenis === 'akun' => route('admin.pengguna.index', ['peran' => User::PEMBIMBING]),
            $role === User::PEMBIMBING && $this->jenis === 'penempatan' => route('pembimbing.penempatan.index'),
            $role === User::PEMBIMBING && $this->jenis === 'laporan' && $ref => route('pembimbing.laporan.show', $ref),
            $role === User::PEMBIMBING && $ref && in_array($this->jenis, ['logbook', 'bimbingan', 'penilaian'], true) => route('pembimbing.bimbingan.show', $ref),
            $role === User::PEMBIMBING => route('pembimbing.bimbingan.index'),
            $role === User::MAHASISWA && in_array($this->jenis, ['pengajuan', 'surat'], true) => route('mahasiswa.pengajuan.index'),
            $role === User::MAHASISWA && in_array($this->jenis, ['kelompok', 'penempatan'], true) => route('mahasiswa.informasi'),
            $role === User::MAHASISWA && $this->jenis === 'logbook' => route('mahasiswa.logbook.index'),
            $role === User::MAHASISWA && $this->jenis === 'laporan' => route('mahasiswa.laporan.index'),
            $role === User::MAHASISWA && $this->jenis === 'penilaian' => route('mahasiswa.penilaian.index'),
            $role === User::MAHASISWA && $this->jenis === 'nilai_akhir' => route('mahasiswa.nilai.index'),
            default => route('dashboard'),
        };
    }
}
