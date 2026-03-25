<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\WorkflowBatch;
use Illuminate\Support\Collection;

class WorkflowStarted
{
    protected Collection $workflows;

    public function __construct($workflows)
    {
        // Normalize input
        if ($workflows instanceof Collection) {
            $this->workflows = $workflows;
        } else {
            $this->workflows = collect([$workflows]);
        }
    }

    public function workflows(): Collection
    {
        return $this->workflows;
    }
}