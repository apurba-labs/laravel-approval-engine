<?php

namespace ApurbaLabs\ApprovalEngine\Contracts;

use Illuminate\Support\Collection;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowNotification;

interface NotificationInterface
{
    public function createNotification($workflow, $stage, $recipient);

    public function dispatch(WorkflowNotification $notification): void;

    public function sendSingle(WorkflowNotification $notification): void;

    public function sendBatch($batch, Collection $notifications): void;

    public function sendImmediateIfNeeded(WorkflowNotification $notification): void;

    public function dispatchAndMaybeSend(WorkflowNotification $notification): void;
}