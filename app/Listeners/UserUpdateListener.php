<?php

namespace App\Listeners;

use App\Events\UserUpdated;
use App\Mail\UserNotificationMail;
use App\Notifications\UserInfoUpdateNotification;
use App\Notifications\UserPasswordUpdateNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserUpdateListener
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
    public function handle(UserUpdated $event): void
    {
        $user = $event->user;

        if ($event->temporaryPassword) {
            $notification = new UserPasswordUpdateNotification($event->temporaryPassword);
            $user->notify($notification);
        }

        if (!$event->notifyPasswordUpdateOnly) {
            $notification = new UserInfoUpdateNotification();
            $user->notify($notification);
        }
    }
}
