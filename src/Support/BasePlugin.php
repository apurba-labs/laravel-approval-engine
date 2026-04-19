<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use ApurbaLabs\ApprovalEngine\Contracts\PluginInterface;

abstract class BasePlugin implements PluginInterface
{
    public function register(): void {}
    public function boot(): void {}
}