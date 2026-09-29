<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $otp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Net-Works admin sign-in code')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Use this one-time code to sign in to the Net-Works administration portal:')
            ->line('**'.$this->otp.'**')
            ->line('The code expires in 10 minutes. If you did not request it, you can ignore this email.');
    }
}
