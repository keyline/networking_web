<?php

namespace App\Notifications;

use App\Models\Enquiries\EnquiryMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessLeadEmailNotification extends Notification
{
    use Queueable;

    public function __construct(public EnquiryMaster $lead, public string $businessName) {}

    public function via(object $notifiable): array { return ['mail']; }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New enquiry for {$this->businessName}")
            ->greeting('You have a new business enquiry')
            ->line("From: {$this->lead->enm_name}")
            ->line('Phone: '.($this->lead->enm_phone ?: 'Not provided'))
            ->line('Email: '.($this->lead->enm_email ?: 'Not provided'))
            ->line("Subject: {$this->lead->enm_subject}")
            ->line($this->lead->enm_description)
            ->action('Open member dashboard', route('dashboard.index'));
    }
}
