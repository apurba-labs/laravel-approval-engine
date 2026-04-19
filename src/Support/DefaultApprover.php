<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use ApurbaLabs\ApprovalEngine\Contracts\ApproverInterface;

class DefaultApprover implements ApproverInterface
{
    public function __construct(
        protected string|int $id,
        protected string $role
    ) {}

    public function getId(): string|int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}