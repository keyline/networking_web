<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Notification;

class EmailNotificationStrategy implements NotificationStrategyInterface
{
    public function send($user, $message)
    {
        Notification::send($user, new EmailNotification($message));
    }
}
