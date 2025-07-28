<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Events\UserSaved;
use App\Notifications\UserPasswordUpdateNotification;
use App\Notifications\UserSavedNotification;

class UserSavedListener
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
    public function handle(UserSaved $event): void
    {
        $user = $event->user;

        if (!$event->notifyPasswordUpdateOnly) {
            $notification = new UserSavedNotification('created');
            $user->notify($notification);
        }

        if ($event->password) {
            $notification = new UserPasswordUpdateNotification($event->password);
            $user->notify($notification);
        }


    }
}
