<?php

namespace App\Notifications;

class NotificationContext
{
    protected $strategy;

    public function setStrategy(NotificationStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function sendNotification($user, $message)
    {
        $this->strategy->send($user, $message);
    }
}
