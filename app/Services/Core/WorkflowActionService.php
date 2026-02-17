<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowActionService
{
    /**
     * Process a workflow action (approval/rejection)
     *
     * @param string $source Table name (e.g., 't_DepartmentNeeds')
     * @param string|int $sourceId Record ID
     * @param int $userId User performing the action
     * @param int $statusId Status from t_CodeDetails
     * @param string|null $notes Optional notes
     * @param string|null $userName Optional user name
     * @param string $statusColumn Column name for status (default: 'Status')
     * @param string|null $enumClass Fully qualified enum class name (optional, for type-safe status updates)
     * @return array Result with status and message
     */
    public function processWorkflowAction(
        string $source,
        string|int $sourceId,
        int $userId,
        int $statusId,
        ?string $notes = null,
        ?string $userName = null,
        string $statusColumn = 'Status',
        ?string $enumClass = null
    ): array {
        try {
            // Call the stored procedure
            $result = DB::select(
                'EXEC p_ProcessWorkflowAction 
                    @Source = ?, 
                    @SourceID = ?, 
                    @UserID = ?, 
                    @UserName = ?, 
                    @Notes = ?, 
                    @StatusColumn = ?, 
                    @StatusID = ?',
                [
                    $source,
                    $sourceId,
                    $userId,
                    $userName,
                    $notes,
                    $statusColumn,
                    $statusId,
                ]
            );

            if (empty($result)) {
                return [
                    'success' => false,
                    'message' => 'No response from workflow processor',
                ];
            }

            $spResult = $result[0];

            // Check if SP returned an error
            if ($spResult->Status === 'ERROR') {
                return [
                    'success' => false,
                    'message' => $spResult->Message,
                ];
            }

            // Update the source table status based on workflow state
            $this->updateSourceTableStatus(
                $source,
                $sourceId,
                $spResult,
                $userId,
                $userName,
                $statusColumn,
                $enumClass
            );

            return [
                'success' => true,
                'message' => $spResult->Message,
                'workflow_status' => $spResult->WorkflowStatus ?? null,
                'is_rejected' => $spResult->IsRejected ?? false,
                'workflow_complete' => $spResult->WorkflowComplete ?? false,
            ];
        } catch (\Exception $e) {
            Log::error('Workflow action failed', [
                'source' => $source,
                'source_id' => $sourceId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update the source table with appropriate status
     */
    protected function updateSourceTableStatus(
        string $source,
        string|int $sourceId,
        object $spResult,
        int $userId,
        ?string $userName,
        string $statusColumn,
        ?string $enumClass
    ): void {
        // Determine the status value
        $statusValue = $this->determineStatusValue($spResult, $enumClass);

        // Build update data
        $updateData = [
            $statusColumn => $statusValue,
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ];

        // Add LastApprover if column exists
        if ($this->columnExists($source, 'LastApprover') && $userName) {
            $updateData['LastApprover'] = $userName;
        }

        // Determine the key column (Id or ID)
        $keyColumn = $this->getKeyColumn($source);

        // Update the source table
        DB::table($source)
            ->where($keyColumn, $sourceId)
            ->update($updateData);
    }

    /**
     * Determine the appropriate status value (enum or direct from t_CodeDetails)
     */
    protected function determineStatusValue(object $spResult, ?string $enumClass): string
    {
        // Determine the status description based on workflow state
        $statusDescription = $this->getStatusDescription($spResult);

        // If enum class is provided and exists, use it
        if ($enumClass && enum_exists($enumClass)) {
            return $this->getEnumValue($enumClass, $statusDescription);
        }

        // Otherwise, get the value directly from t_CodeDetails
        return $this->getCodeDetailValue($statusDescription);
    }

    /**
     * Get status description based on workflow state
     */
    protected function getStatusDescription(object $spResult): string
    {
        // If rejected
        if (isset($spResult->IsRejected) && $spResult->IsRejected == 1) {
            return 'Rejected';
        }

        // If workflow is complete (all approvals done)
        if (isset($spResult->WorkflowComplete) && $spResult->WorkflowComplete == 1) {
            return 'Approved';
        }

        // Default to pending (still has pending approvals or uncertain state)
        return 'Pending';
    }

    /**
     * Get enum value by matching description to case label
     */
    protected function getEnumValue(string $enumClass, string $statusDescription): string
    {
        // Get all enum cases
        $cases = $enumClass::cases();

        foreach ($cases as $case) {
            // Check if enum has a label() method (common pattern)
            if (method_exists($case, 'label') && $case->label() === $statusDescription) {
                return $case->value;
            }

            // Fallback: match by case name
            if ($case->name === $statusDescription) {
                return $case->value;
            }
        }

        // If no match found, return the first case as fallback
        return $cases[0]->value;
    }

    /**
     * Get value from t_CodeDetails by description
     */
    protected function getCodeDetailValue(string $description): string
    {
        $result = DB::table('t_CodeDetails')
            ->where('Description', $description)
            ->orWhere('Description', strtolower($description))
            ->first(['Value']);

        return $result?->Value ?? strtolower($description[0]);
    }

    /**
     * Check if a column exists in a table
     */
    protected function columnExists(string $tableName, string $columnName): bool
    {
        $table = str_replace(['[', ']'], '', $tableName);

        $result = DB::select(
            "SELECT COUNT(*) as count
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $columnName]
        );

        return $result[0]->count > 0;
    }

    /**
     * Get the primary key column name (Id or ID)
     */
    protected function getKeyColumn(string $tableName): string
    {
        $table = str_replace(['[', ']'], '', $tableName);

        $result = DB::select(
            "SELECT COLUMN_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_NAME = ? AND COLUMN_NAME IN ('Id', 'ID')
             ORDER BY COLUMN_NAME",
            [$table]
        );

        return $result[0]->COLUMN_NAME ?? 'Id';
    }
}
