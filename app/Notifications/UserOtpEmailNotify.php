<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserOtpEmailNotify extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private ?string $recipientName = null,
        private ?string $otp = null,
        private bool $contactChange = false,
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->recipientName ?: (trim(($notifiable->userDetail?->ud_first_name ?? '').' '.($notifiable->userDetail?->ud_last_name ?? '')) ?: 'Member');

        $subject = $this->contactChange ? 'Verify your new Networx email' : 'Your Networx login OTP';

        return (new MailMessage())
                    ->subject($subject)
                    ->view('email-templates.login-otp', [
                        'name' => $name,
                        'otp' => $this->otp ?: $notifiable->um_otp,
                        'contactChange' => $this->contactChange,
                        'subject' => $subject,
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
