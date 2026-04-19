<?php
namespace ApurbaLabs\ApprovalEngine\Contracts;

/**
 * Interface WorkflowSubjectInterface
 *
 * Represents the subject of a workflow, which can be any entity that requires approval.
 * This is the core bridge between engine and application, allowing the engine to interact with various types of subjects in a consistent way.  
 */
interface WorkflowSubjectInterface
{
    public function getId(): string|int;

    public function getType(): string;

    public function getPayload(): array;
}