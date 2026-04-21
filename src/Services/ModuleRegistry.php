<?php
namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;

class ModuleRegistry
{
    protected array $modules = [];

    public function register(WorkflowModuleInterface $module): void
    {
        $this->modules[$module->name()] = $module;
    }

    public function get(string $name): ?WorkflowModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    public function all(): array
    {
        return $this->modules;
    }
    public function clear(): void
    {
        $this->modules = [];
    }
}