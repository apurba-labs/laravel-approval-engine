<?php
namespace ApurbaLabs\ApprovalEngine\Services;

use ApurbaLabs\ApprovalEngine\Contracts\PluginInterface;

class PluginManager
{
    protected array $plugins = [];

    public function register(PluginInterface $plugin): void
    {
        $this->plugins[] = $plugin;
        $plugin->register();
    }

    public function boot(): void
    {
        foreach ($this->plugins as $plugin) {
            $plugin->boot();
        }
    }
}