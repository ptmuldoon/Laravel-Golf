<?php

namespace App\Mail;

use App\Models\League;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeagueMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public League $league,
        public string $messageSubject,
        public string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->league->name} - {$this->messageSubject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.league-message',
        );
    }
}
