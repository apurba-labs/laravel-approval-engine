<?php

namespace ApurbaLabs\ApprovalEngine\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface WorkflowModuleInterface
{
    /**
     * The fully qualified modules name a consistent identifier for following criteria:
     * USE CASE 1: Modules must have a consistent identifier.
     * USE CASE 2: Batch Creation.
     * USE CASE 3: Database Storage.
     * USE CASE 4: Database Storage
     */
    public function name(): string;
    
    /**
     * The fully qualified class name of the Eloquent model.
     */
    public function model(): string;

    /**
     * Get workflow settings for the module.
     * 
     * @param string|null $role
     * @return \Illuminate\Support\Collection|\ApurbaLabs\ApprovalEngine\Models\WorkflowSetting|null
     */
    public function getSettings(?string $role = null);

    /**
     * Validate data before batching.
     * 
     * @param array $data
     * @throws \Exception
     */
    public function validate(array $data): void;

    /**
     * Define the priority list of relationship names that represent the owner.
     */
    public function ownerRelations(): array;

    /**
     * The column name that tracks the approval state (e.g., 'is_approved').
     */
    public function approvedColumn(): string;

     /** The state column (status) */
    public function statusColumn(): string;

    /**
     * Relationships that should be eager-loaded to avoid N+1 issues.
     */
    public function relations(): array;

    /** Columns to fetch from DB: ['id', 'amount', 'user_id'] */
    public function selectColumns(): array;


    /**
     * Mapping of database columns to human-readable labels for the UI/Email.
     * Example: ['amount' => 'Total Amount', 'user.name' => 'Requested By']
     */
    public function displayColumns(): array;

    /**
     * The base query used to fetch records for the workflow.
     */
    public function query(): Builder;
}
