<?php

namespace App\Notifications;

use App\Notifications\Channels\SmsChannel;
use Illuminate\Notifications\Notification;

class PhoneVerificationNotification extends Notification
{
    public function __construct(private string $otp) {}

    public function via(object $notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms(object $notifiable): string
    {
        return "CedarGov: Your verification code is {$this->otp}. Valid for 5 minutes. Do not share this code.";
    }
}
