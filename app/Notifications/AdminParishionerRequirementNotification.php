<?php

namespace App\Notifications;

use App\Models\Parishioner;
use App\Models\ServiceRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminParishionerRequirementNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Parishioner        $parishioner,
        private ServiceRequirement $requirement,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => 'New Requirement Declaration',
            'message' => $this->parishioner->full_name . ' submitted "' . $this->requirement->name . '".',
            'icon'    => 'document',
            'url'     => url('/admin/parishioner-requirements'),
        ];
    }
}
