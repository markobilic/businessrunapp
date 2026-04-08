<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Spatie\WelcomeNotification\WelcomeNotification;
use Illuminate\Support\Facades\Lang;

class MyCustomWelcomeNotification extends WelcomeNotification
{
    public function buildWelcomeNotificationMessage(): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Welcome to Business Run Series'))
            ->line(Lang::get('You are receiving this email because an account was created for you.'))
            ->action(Lang::get('Set initial password'), $this->showWelcomeFormUrl)
            ->line(Lang::get('This welcome link will expire in :count hours.', ['count' => round($this->validUntil->diffInRealMinutes() / 60)]));
    }
}
