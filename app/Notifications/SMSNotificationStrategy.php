<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Notification;

class SMSNotificationStrategy implements NotificationStrategyInterface
{
    public function send($user, $message)
    {
        // Assuming notification channels are properly set.
        Notification::send($user, new SMSNotification($message));
    }
}
