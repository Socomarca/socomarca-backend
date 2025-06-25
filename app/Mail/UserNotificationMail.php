<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class   UserNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * El usuario que recibe la notificación.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * El tipo de acción realizada (created, updated).
     *
     * @var string
     */
    public $action;

    /**
     * La contraseña
     *
     * @var string|null
     */
    public ?string $password;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $action, ?string $password = null)
    {
        $this->user = $user;
        $this->action = $action;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->action === 'created' 
            ? 'Bienvenido a Socomarca - Cuenta Creada'
            : 'Información de tu cuenta actualizada - Socomarca';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 