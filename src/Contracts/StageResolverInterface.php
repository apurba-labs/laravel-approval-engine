<?php

namespace ApurbaLabs\ApprovalEngine\Contracts;

interface StageResolverInterface
{
    /**
     * Resolve stages dynamically for a module
     *
     * @param object|string $module
     * @param array $stages
     * @return array
     */
    public function resolve($module, array $stages): array;
}
