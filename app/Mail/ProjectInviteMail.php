<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;

    public function __construct(\App\Models\Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        return $this->view('pages.invitation')  // A view que será usada para o conteúdo do e-mail
                    ->with([
                        'projectName' => $this->invitation->getProject->name,
                        'inviteLink' => route('invitation.accept', ['invitation' => $this->invitation->id]),
                    ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Project Invite Mail',
        );
    }

    /**
     * Get the message content definition.
     */


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
