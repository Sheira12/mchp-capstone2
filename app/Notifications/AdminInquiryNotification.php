<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database notification sent to ALL admin users when a visitor
 * submits an inquiry through the public contact form.
 */
class AdminInquiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $name,
        private string $email,
        private string $subject,
        private string $message,
        private int    $inquiryId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'sender_name'  => $this->name,
            'sender_email' => $this->email,
            'subject'      => $this->subject,
            'message'      => $this->name . ' sent an inquiry: "' . $this->subject . '".',
            'url'          => route('admin.inquiries.show', $this->inquiryId),
            'notif_type'   => 'inquiry',
        ];
    }
}
