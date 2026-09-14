<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    const STATUSES = [
        'new'         => 'New',
        'read'        => 'Under Review',
        'in_progress' => 'In Progress',
        'replied'     => 'Replied',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
    ];
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'preferred_date', 'preferred_time',
        'attachments',
        'status', 'replies', 'replied_at',
    ];

    protected $casts = [
        'attachments'  => 'array',
        'replies'      => 'array',
        'preferred_date' => 'date',
        'replied_at'   => 'datetime',
    ];

    public function isNew(): bool     { return $this->status === 'new'; }
    public function isRead(): bool    { return $this->status === 'read'; }
    public function isReplied(): bool { return $this->status === 'replied'; }

    /**
     * Append a reply to the thread and persist.
     *
     * @param array $adminAttachments  [{original_name, path, mime, size}]
     */
    public function addReply(string $adminName, string $message, array $adminAttachments = [], string $replySubject = ''): void
    {
        $replies   = $this->replies ?? [];
        $replies[] = [
            'admin_name'   => $adminName,
            'reply_subject'=> $replySubject ?: $this->subject,
            'message'      => $message,
            'sent_at'      => now()->toDateTimeString(),
            'attachments'  => $adminAttachments,
        ];
        $this->replies    = $replies;
        $this->status     = 'replied';
        $this->replied_at = now();
        $this->save();
    }
}
