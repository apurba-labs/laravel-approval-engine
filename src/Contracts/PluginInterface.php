<?php
namespace ApurbaLabs\ApprovalEngine\Contracts;

interface PluginInterface
{
    public function register(): void;

    public function boot(): void;
}