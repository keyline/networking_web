<?php

namespace App\Notifications;

use App\Services\KeylineSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SMSNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return [SMSChannel::class];
    }
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function toSms(object $notifiable)
    {
        // We are assuming we are notifying a user or a model that has a telephone attribute/field.
        // And the telephone number is correctly formatted.
        // TODO: SmsMessage, doesn't exist yet :-) We should create it.
        return (new KeylineSmsService())
                    //->from('ObiWan')
                    ->to($notifiable->uro_mobileno)
                    ->line("Dear User, $notifiable->uro_otp is you verification OTP for registration at KEYLINE");
        //->line("Do not share this OTP with anyone for security reasons.");


    }
}
