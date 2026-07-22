<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database notification stored for the parishioner user.
 * Shows in the portal notification bell.
 */
class ParishionerStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private string $url = '',
        private string $icon = 'bell', // bell | check | calendar | document
    ) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url ?: route('parishioner.dashboard'),
            'icon'    => $this->icon,
        ];
    }
}
