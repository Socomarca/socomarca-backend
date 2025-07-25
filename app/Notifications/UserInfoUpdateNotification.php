<?php

namespace App\Notifications;

use App\Mail\UserNotificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class UserInfoUpdateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     * @throws \Exception
     */
    public function toMail(object $notifiable): Mailable
    {
        if ($notifiable instanceof User) {
            return (new UserNotificationMail(
                $notifiable,
                'updated',
            ))->to($notifiable->email);
        } else {
            throw new \Exception('Notifiable must be an instance of User');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user' => $notifiable,
        ];
    }
}
