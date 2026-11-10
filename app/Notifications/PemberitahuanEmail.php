<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Satu kelas notifikasi email yang dipakai untuk semua pemberitahuan PrakTI:
 * status pengajuan, penugasan pembimbing, feedback logbook/progress, komentar baru.
 */
class PemberitahuanEmail extends Notification
{
    use Queueable;

    public function __construct(
        public string $subjek,
        public array $baris,
        public ?string $url = null,
        public string $tombol = 'Buka PrakTI',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pesan = (new MailMessage)
            ->subject($this->subjek.' | PrakTI')
            ->greeting('Halo, '.$notifiable->name);

        foreach ($this->baris as $baris) {
            $pesan->line($baris);
        }

        if ($this->url) {
            $pesan->action($this->tombol, $this->url);
        }

        return $pesan->salutation('Salam, Tim PrakTI Program Studi Informatika');
    }
}
