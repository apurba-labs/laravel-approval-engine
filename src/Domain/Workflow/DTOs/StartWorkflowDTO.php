<?php
namespace ApurbaLabs\ApprovalEngine\Domain\Workflow\DTOs;

class StartWorkflowDTO
{
    public function __construct(
        public string $module,
        public array $payload
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            module: $data['module'],
            payload: $data['payload'] ?? []
        );
    }
}