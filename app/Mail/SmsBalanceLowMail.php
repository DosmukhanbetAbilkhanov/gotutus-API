<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmsBalanceLowMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $phone,
        public readonly string $errorMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[URGENT] SMS Balance Depleted — Users Cannot Receive Codes',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sms-balance-low',
        );
    }
}
