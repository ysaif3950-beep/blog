<?php

namespace App\Listeners;

use App\Events\ResetPasswordEvent;

class SendResetPasswordEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ResetPasswordEvent $event): void
    {
        $event->user->sendPasswordResetNotification($event->token);
    }
}
