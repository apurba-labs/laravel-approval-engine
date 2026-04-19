<?php
namespace ApurbaLabs\ApprovalEngine\Contracts;

interface ContextResolverInterface
{
    public function get(string $key): mixed;
}