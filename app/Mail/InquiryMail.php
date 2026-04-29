<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $inquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Parish Inquiry: ' . $this->inquiry['subject'],
            replyTo: [$this->inquiry['email'] => $this->inquiry['name']],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry');
    }
}
