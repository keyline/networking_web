<?php

namespace App\Notifications;

use App\Models\Enquiries\EnquiryMaster;
use App\Services\KeylineSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BusinessLeadSmsNotification extends Notification
{
    use Queueable;

    public function __construct(public EnquiryMaster $lead, public string $businessName, public ?string $mobile = null) {}
    public function via(object $notifiable): array { return [SMSChannel::class]; }
    public function toSms(object $notifiable): KeylineSmsService
    {
        $number = $this->mobile ?: $notifiable->um_mobile_no;
        return (new KeylineSmsService)->to($number)->line("New Net-Works enquiry for {$this->businessName} from {$this->lead->enm_name}. Phone: ".($this->lead->enm_phone ?: 'not provided').'. Sign in to view details.');
    }
}
