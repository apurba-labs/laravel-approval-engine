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
     * @var WorkflowBatch|Collection
     */
    public $workflows;

    /**
     * Track if this was intended as a batch start
     */
    protected bool $wasBatch;

    /**
     * @param WorkflowBatch|Collection $workflows
     */
    public function __construct($workflows)
    {
        $this->workflows = $workflows;
        
        // Explicitly detect if it started as a collection
        $this->wasBatch = $workflows instanceof Collection;
    }

    /**
     * Check if this event was triggered as a batch.
     */
    public function isBatch(): bool
    {
        return $this->wasBatch;
    }

    /**
     * Normalizes the output so Listeners can always loop safely.
     */
    public function getWorkflows(): Collection
    {
        if ($this->workflows instanceof Collection) {
            return $this->workflows;
        }

        return collect([$this->workflows]);
    }
}
