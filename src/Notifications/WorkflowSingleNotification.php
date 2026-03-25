<?php

namespace ApurbaLabs\ApprovalEngine\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowSingleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $notification) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('New Approval Request')
            ->line("Module: {$this->notification->module}")
            ->line("Role: {$this->notification->role}");
    }
}