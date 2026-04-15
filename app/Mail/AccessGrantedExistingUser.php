<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessGrantedExistingUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nome,
        public string $planId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Banco de Choices — Acceso actualizado / Acesso atualizado',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.access-granted',
        );
    }
}
