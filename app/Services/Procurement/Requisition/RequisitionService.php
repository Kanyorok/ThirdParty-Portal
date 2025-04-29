<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Procurement\Requisitions;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequisitionService {
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function addRequisition($branch, $department, $remarks, $category, User $actor)
    {
        try {
            // Start transaction and execute the stored procedure
             DB::transaction(function () use ($branch, $department, $remarks, $category, $actor) {

                DB::statement('EXEC p_AddRequisition ?, ?, ?, ?, ?', [
                    $branch,
                    $department,
                    $remarks,
                    $category,
                    $actor->Id // Pass the User ID, not the entire User model
                ]);
            });

            return [
                'status' => 'success',
                'message' => 'Requisition successfully created.'
            ];

        } catch (QueryException $e) {
            // Log the SQL error
            Log::error('SQL Error executing p_AddRequisition', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return the error message back to the controller
            return [
                'status' => 'error',
                'message' => 'SQL error executing requisition creation',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            // Log the exception for debugging
            Log::error('Error executing p_AddRequisition', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return a custom error message or handle as needed
            return[
                'status' => 'error',
                'message' => 'Error executing requisition creation',
                'error' => $e->getMessage()
            ];
        }
    }
//
    public static function fetchRequisition()
    {
        return DB::table(DB::raw('t_Requisitions WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_RequisitionLines WITH (NOLOCK)'), 't_Requisitions.id', '=', 't_RequisitionLines.RequisitionId')
            ->select(DB::raw('
                t_Requisitions.RequisitionNo,
                t_Requisitions.BranchID,
                t_Requisitions.DepartmentID,
                t_Requisitions.Remarks,
                t_Requisitions.Status,
                t_Requisitions.Category,
                t_Requisitions.CreatedOn,
                SUM(t_RequisitionLines.ExpectedPrice) as ExpectedPrice,
                COUNT(t_RequisitionLines.Id) as itemcount
            '))
            ->groupBy(
                't_Requisitions.RequisitionNo',
                't_Requisitions.BranchID',
                't_Requisitions.DepartmentID',
                't_Requisitions.Remarks',
                't_Requisitions.Status',
                't_Requisitions.Category',
                't_Requisitions.CreatedOn'
            )
            ->get();
    }
}
