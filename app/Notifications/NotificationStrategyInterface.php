<?php

namespace App\Notifications;

interface NotificationStrategyInterface
{
    public function send($user, $message);
}
