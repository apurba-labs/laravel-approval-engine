<?php
namespace ApurbaLabs\ApprovalEngine\Support;

class HookManager
{
    protected array $hooks = [];

    public function add(string $name, callable $callback): void
    {
        $this->hooks[$name][] = $callback;
    }

    public function apply(string $name, $value, ...$args)
    {
        foreach ($this->hooks[$name] ?? [] as $callback) {
            $value = $callback($value, ...$args);
        }

        return $value;
    }
}