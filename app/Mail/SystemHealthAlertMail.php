<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemHealthAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $problems
     */
    public function __construct(
        public readonly array $problems,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ALERT] Tanys system health check failed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system-health-alert',
        );
    }
}
