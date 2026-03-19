<?php

namespace ApurbaLabs\ApprovalEngine\Actions;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use Illuminate\Database\Eloquent\Collection;

class FetchApprovedRecordsAction
{
    public function execute(
        WorkflowModuleInterface $module,
        $start = null,
        $end = null
    ): Collection {

        // Start with the base query from the module
        $query = $module->query()
            ->select($module->selectColumns())
            ->with($module->relations());

        // Filter by Status
        $statusColumn = $module->statusColumn();
        if ($statusColumn) {
            $query->where($statusColumn, 'approved');
        }

        // Apply the DateTime window (The "When")
        $approvedColumn = $module->approvedColumn();
        if ($start && $end && $approvedColumn) {
            $query->whereBetween($approvedColumn, [$start, $end]);
        }

        return $query->get();
    }
}
