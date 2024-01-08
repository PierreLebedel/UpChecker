<?php

namespace App\Mail;

use App\Models\Action;
use App\Models\Behavior;
use App\Models\Checkup;
use App\Models\Endpoint;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckupActionEmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Behavior $behavior;

    public Endpoint $endpoint;

    public Project $project;

    public function __construct(public Action $action, public Checkup $checkup)
    {
        $this->behavior = $this->action->behavior;
        $this->endpoint = $this->behavior->endpoint;
        $this->project  = $this->endpoint->project;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').': Checkup for project '.$this->project->name,
            to: $this->action->params['email_to'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mails.checkup-action-email',
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
