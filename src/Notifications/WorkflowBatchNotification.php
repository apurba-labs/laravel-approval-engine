<?php

namespace ApurbaLabs\ApprovalEngine\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class WorkflowBatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $batch, public $notifications) {}

    public function via($notifiable)
    {
        return ['mail']; // later: slack, db
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("You have {$this->notifications->count()} pending approvals")
            ->line("Module: {$this->batch->module}")
            ->line("Total items: {$this->notifications->count()}");
    }
}
