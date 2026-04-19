<?php
namespace ApurbaLabs\ApprovalEngine\Contracts;

interface ApproverInterface
{
    public function getId(): string|int;

    public function getRole(): string;
}