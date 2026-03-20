<?php

namespace ApurbaLabs\ApprovalEngine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\WorkflowBatch;
use Illuminate\Support\Collection;

class WorkflowStarted
{
    use Dispatchable, SerializesModels;

    /**
     * @var WorkflowBatch|Collection  Single or multiple workflow(s)
     */
    public $workflows;

    /**
     * Create a new event instance.
     *
     * @param WorkflowBatch|Collection $workflows
     */
    public function __construct($workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * Check if this event contains a batch of workflows.
     *
     * @return bool
     */
    public function isBatch(): bool
    {
        return $this->workflows instanceof Collection;
    }

    /**
     * Always return a Collection of workflows, even if single.
     *
     * @return Collection
     */
    public function workflows(): Collection
    {
        return $this->isBatch() ? $this->workflows : collect([$this->workflows]);
    }
}
