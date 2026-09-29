<?php

namespace App\Notifications;

use App\Services\KeylineSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class LoginSMSNotification extends Notification
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
        $userDetails = $notifiable->userDetail()
                        ->select(DB::raw("
                            CASE 
                            WHEN ud_salutation IS NOT NULL AND ud_salutation != '' 
                            THEN CONCAT(ud_salutation, ' ', ud_first_name) 
                            ELSE ud_first_name 
                            END AS ud_first_name
                        "))
                        ->first();


        $recipientName = trim((string) ($userDetails?->ud_first_name ?: $notifiable->um_user_name ?: 'Member'));

        return (new KeylineSmsService())
                //->from('ObiWan')
                ->to($notifiable->um_mobile_no)
                ->line("Dear {$recipientName}, {$notifiable->um_otp} is your Net-Works login OTP. It expires in 10 minutes. Do not share it with anyone.");
        //->line("Do not share this OTP with anyone for security reasons.");


    }
}
