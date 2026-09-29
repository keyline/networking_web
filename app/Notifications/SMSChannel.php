<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SMSChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toSms($notifiable);


        // Now we hopefully have a instance of a SmsMessage.
        // That we are ready to send to our user.
        // Let's do it :-)
        $message->send();

        // Or use dryRun() for testing to send it, without sending it for real.
        //$message->dryRun()->send();

        // Wait.. was that it?
        // Well sort of.. :-)
        // But we need to implement this magical SmsMessage class for it to work.

    }
}
