<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation
    ) {}

    public function build()
    {
        $frontendUrl = rtrim(
            config('app.frontend_url', 'http://localhost:4200'),
            '/'
        );

        $acceptUrl = $frontendUrl
            . '/accept-invitation?token='
            . urlencode($this->invitation->token);

        return $this->subject('Workspace Invitation')
            ->view('emails.workspace-invitation')
            ->with([
                'invitation' => $this->invitation,
                'acceptUrl' => $acceptUrl,
            ]);
    }
}
