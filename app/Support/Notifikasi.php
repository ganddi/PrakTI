<?php

namespace App\Support;

use App\Notifications\PemberitahuanEmail;
use Illuminate\Support\Facades\Notification;

class Notifikasi
{
    /**
     * Kirim email pemberitahuan. Kegagalan pengiriman (misalnya SMTP belum diatur)
     * dicatat di log dan tidak menggagalkan proses utama.
     *
     * @param  \App\Models\User|iterable|null  $penerima
     */
    public static function kirim($penerima, string $subjek, array $baris, ?string $url = null, string $tombol = 'Buka PrakTI'): void
    {
        $daftar = collect(is_iterable($penerima) ? $penerima : [$penerima])->filter();

        if ($daftar->isEmpty()) {
            return;
        }

        try {
            Notification::send($daftar, new PemberitahuanEmail($subjek, $baris, $url, $tombol));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
