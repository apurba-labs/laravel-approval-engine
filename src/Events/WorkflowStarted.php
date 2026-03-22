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
     * @param WorkflowBatch|Collection $workflows
     */
    public function __construct($workflows)
    {
        $this->workflows = $workflows;
        
    }
    
    public function workflows(): Collection
    {

        return collect([$this->workflows]);
    }
}
