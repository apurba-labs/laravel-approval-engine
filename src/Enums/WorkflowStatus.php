<?php

namespace ApurbaLabs\ApprovalEngine\Enums;

enum WorkflowStatus: string
{
    case DRAFT = 'draft';
    case NEW = 'new';
    case PENDING = 'pending';
    case ON_HOLD = 'on_hold';
    case PROCESSING = 'processing';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REMOVED = 'removed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::NEW => 'New',
            self::PENDING => 'Pending',
            self::ON_HOLD => 'On Hold',
            self::PROCESSING => 'Processing',
            self::APPROVED => 'Approved',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REMOVED => 'Removed',
        };
    }
}
