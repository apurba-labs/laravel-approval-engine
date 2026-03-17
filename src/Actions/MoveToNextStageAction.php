<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Support\Str;

class MoveToNextStageAction
{
    public function execute($batch, int $stage)
    {
        return WorkflowBatch::create([
            'module'=>$batch->module,
            'stage'=>$stage,
            'token'=>Str::uuid(),
            'status'=>'pending',
            'window_start'=>now(),
            'window_end'=>now()->addHours(24)
        ]);
    }
}
