<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatEscalationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $sessionId,
        public string $lastMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Chat Escalation - Parish Website');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.chat-escalation');
    }
}
