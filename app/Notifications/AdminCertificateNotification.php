<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminCertificateNotification extends Notification
{
    use Queueable;

    public function __construct(private Certificate $certificate) {}

    public function via($notifiable): array { return ['database']; }

    public function toDatabase($notifiable): array
    {
        return [
            'certificate_id'   => $this->certificate->id,
            'cert_number'      => $this->certificate->certificate_number,
            'parishioner_name' => $this->certificate->parishioner->full_name,
            'type'             => $this->certificate->getTypeLabel(),
            'purpose'          => $this->certificate->purpose,
            'message'          => $this->certificate->parishioner->full_name
                . ' requested a ' . $this->certificate->getTypeLabel() . '.',
            'url'              => route('admin.certificates.show', $this->certificate->id),
            'notif_type'       => 'certificate_request',
        ];
    }
}
