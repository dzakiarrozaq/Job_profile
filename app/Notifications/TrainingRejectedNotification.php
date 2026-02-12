<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingRejectedNotification extends Notification
{
    use Queueable;

    public $item;
    public $reason;

    public function __construct($item, $reason)
    {
        $this->item = $item;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail']; // Kirim via email
    }

    public function toMail($notifiable)
    {
        $trainingTitle = $this->item->title ?? $this->item->training->title ?? 'Pelatihan';

        return (new MailMessage)
                    ->subject('Penolakan Item Pelatihan')
                    ->greeting('Halo, ' . $notifiable->name)
                    ->line('Item pelatihan berikut telah DITOLAK oleh supervisor:')
                    ->line('**' . $trainingTitle . '**')
                    ->line('Alasan Penolakan: ' . $this->reason)
                    ->line('Silakan revisi pengajuan Anda jika diperlukan.')
                    ->action('Lihat Rencana Pelatihan', route('training.index')); // Sesuaikan route user
    }
}