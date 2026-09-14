<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AdminInquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inquiry $inquiry,
        public string  $replyMessage,
        public string  $adminName,
        public array   $adminAttachments = []   // [{original_name, path, mime, size}]
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: ' . $this->inquiry->subject . ' — ' . config('parish.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry-reply');
    }

    /** @return array<Attachment> */
    public function attachments(): array
    {
        $attached = [];
        foreach ($this->adminAttachments as $file) {
            $fullPath = Storage::disk('public')->path($file['path']);
            if (file_exists($fullPath)) {
                $attached[] = Attachment::fromPath($fullPath)
                    ->as($file['original_name'])
                    ->withMime($file['mime']);
            }
        }
        return $attached;
    }
}
