<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nome,
        public string $email,
        public string $password,
        public float $totalPrice,
        public string $planId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Banco de Choices — Credenciales de acceso / Credenciais de acesso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-new-user',
        );
    }
}
