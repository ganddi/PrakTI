<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Salinan email dari notifikasi PrakTI. Notifikasi di aplikasi disimpan di tabel notifikasi (SDD Tabel 3.15). */
class PemberitahuanEmail extends Notification
{
    use Queueable;

    public function __construct(public string $subjek, public array $baris, public ?string $url = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pesan = (new MailMessage)->subject($this->subjek.' | PrakTI')->greeting('Halo, '.$notifiable->name);
        foreach ($this->baris as $b) {
            $pesan->line($b);
        }
        if ($this->url) {
            $pesan->action('Buka PrakTI', $this->url);
        }

        return $pesan->salutation('Salam, Tim PrakTI Program Studi Informatika');
    }
}
