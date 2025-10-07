<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $otp)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your One-Time Password (OTP)')
            ->line('Your one-time password (OTP) for authentication is:')
            ->line('**' . $this->otp . '**')
            ->line('This code will expire in 5 minutes. Do not share it.')
            ->line('If you did not request this, please ignore this email.');
    }
}