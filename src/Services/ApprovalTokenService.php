<?php

namespace ApurbaLabs\ApprovalEngine\Services;

use Illuminate\Support\Str;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\ApprovalToken;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class ApprovalTokenService
{
    public function create(WorkflowInstance $workflow, ?int $userId = null): ApprovalToken
    {
        return ApprovalToken::create([
            'workflow_instance_id' => $workflow->id,
            'user_id' => $userId,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
        ]);
    }

    public function validate(string $token): ApprovalToken
    {
        $record = ApprovalToken::where('token', $token)->first();

        if (!$record) {
            throw new \RuntimeException('Invalid token');
        }

        if ($record->used_at) {
            throw new \RuntimeException('Token already used');
        }

        if ($record->expires_at && now()->gt($record->expires_at)) {
            throw new \RuntimeException('Token expired');
        }

        return $record;
    }

    public function markUsed(ApprovalToken $token): void
    {
        $token->forceFill([
            'used_at' => now(),
        ])->save();
    }
}