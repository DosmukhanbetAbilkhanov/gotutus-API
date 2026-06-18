<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ExceptionAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $exceptionClass,
        public readonly string $message,
        public readonly string $location,
        public readonly ?string $url = null,
        public readonly ?string $method = null,
        public readonly ?string $trace = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ERROR] '.class_basename($this->exceptionClass).': '.Str::limit($this->message, 80),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exception-alert',
        );
    }
}
