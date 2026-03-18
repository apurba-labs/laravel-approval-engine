<?php

namespace ApurbaLabs\ApprovalEngine\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;


class BatchApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;
    public $records;
    public $module;

    /**
     * Create a new message instance.
     */
    public function __construct(WorkflowBatch $batch, Collection $records, $module)
    {
        $this->batch = $batch;
        $this->records = $records;
        $this->module = $module;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Approval Batch Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'approval-engine::emails.batch-approval',
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
