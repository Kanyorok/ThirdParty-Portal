<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Auth\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequisitionService
{
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
            return [
                'status' => 'error',
                'message' => 'Error executing requisition creation',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getItemTypes()
    {
        try {
            return DB::table('t_ItemTypes')
                ->join('t_CodeDetails', 't_ItemTypes.TypeName', '=', 't_CodeDetails.Id')
                ->select('t_ItemTypes.Id', 't_CodeDetails.Description as TypeName')
                ->where('t_ItemTypes.Active', true)
                ->whereNull('t_ItemTypes.DeletedOn')
                ->orderBy('t_CodeDetails.Description')
                ->get();
        } catch (QueryException $e) {
            Log::error('Error fetching item types: ' . $e->getMessage());
            return collect(); // Return an empty collection on error
        }
    }
    //
    public static function fetchRequisition()
    {
        return DB::table(DB::raw('t_Requisitions WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_RequisitionLines WITH (NOLOCK)'), 't_Requisitions.id', '=', 't_RequisitionLines.RequisitionId')
            ->leftJoin(DB::raw('t_CodeDetails WITH (NOLOCK)'), 't_Requisitions.StatusID', '=', 't_CodeDetails.ID')
            ->leftJoin(DB::raw('t_Branches WITH (NOLOCK)'), 't_Requisitions.BranchID', '=', 't_Branches.Id')
            ->leftJoin(DB::raw('t_Departments WITH (NOLOCK)'), 't_Requisitions.DepartmentID', '=', 't_Departments.Id')
            ->leftJoin(DB::raw('t_ConsolidatedProcurementPlan WITH (NOLOCK)'), 't_Requisitions.PlanRef', '=', 't_ConsolidatedProcurementPlan.PlanID')
            ->select(DB::raw('
            t_Requisitions.RequisitionNo,
            COALESCE(t_Branches.Name,t_Requisitions.BranchID) as BranchID ,
            COALESCE(t_Departments.Name,t_Requisitions.DepartmentID) as DepartmentID ,
            t_Requisitions.Remarks,
            CASE 
                WHEN t_Requisitions.DocStatus = \'Ap\' THEN \'Approved\'
                WHEN t_Requisitions.DocStatus = \'AP\' THEN \'Approved\'
                WHEN t_Requisitions.DocStatus = \'pe\' THEN \'Pending\'
                WHEN t_Requisitions.DocStatus = \'Re\' THEN \'Rejected\'
                WHEN t_Requisitions.DocStatus = \'RE\' THEN \'Rejected\'
                ELSE t_CodeDetails.Description 
            END as Status,
            t_Requisitions.CreatedOn,
            t_Requisitions.Id,
            -- Sum of (Quantity * ExpectedPrice) to get the total cost per requisition
            SUM(ISNULL(t_RequisitionLines.Quantity, 0) * ISNULL(t_RequisitionLines.ExpectedPrice, 0)) as ExpectedPrice,
            COUNT(t_RequisitionLines.Id) as itemcount,
            t_ConsolidatedProcurementPlan.Title + \' - \' + t_ConsolidatedProcurementPlan.ReferenceNumber as PlanTitle
        '))
            ->groupBy(
                't_Requisitions.Id',
                't_Requisitions.RequisitionNo',
                't_Requisitions.BranchID',
                't_Requisitions.DepartmentID',
                't_Requisitions.Remarks',
                't_CodeDetails.Description',
                't_Requisitions.CreatedOn',
                't_Departments.Name',
                't_Branches.Name',
                't_ConsolidatedProcurementPlan.Title',
                't_ConsolidatedProcurementPlan.ReferenceNumber',
                't_Requisitions.DocStatus'
            )
            ->get();
    }


    public static function getRelatedRequisition($RequisitionId)
    {
        return DB::table(DB::raw('t_Requisitions WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_RequisitionLines WITH (NOLOCK)'), 't_Requisitions.Id', '=', 't_RequisitionLines.RequisitionId')
            ->leftJoin(DB::raw('t_CodeDetails WITH (NOLOCK)'), 't_Requisitions.StatusID', '=', 't_CodeDetails.ID')
            ->leftJoin(DB::raw('t_Branches WITH (NOLOCK)'), 't_Requisitions.BranchID', '=', 't_Branches.Id')
            ->leftJoin(DB::raw('t_Departments WITH (NOLOCK)'), 't_Requisitions.DepartmentID', '=', 't_Departments.Id')
            ->leftJoin(DB::raw('t_ConsolidatedProcurementPlan WITH (NOLOCK)'), 't_Requisitions.PlanRef', '=', 't_ConsolidatedProcurementPlan.PlanID')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Requisitions.CreatedBy', '=', 't_Users.Id')
            ->where('t_Requisitions.Id', $RequisitionId)
            ->select([
                't_Requisitions.RequisitionNo',
                DB::raw('COALESCE(t_Branches.Name, t_Requisitions.BranchID) AS BranchID'),
                DB::raw('COALESCE(t_Departments.Name, t_Requisitions.DepartmentID) AS DepartmentID'),
                't_Requisitions.Remarks',
                DB::raw("CASE 
                    WHEN t_Requisitions.DocStatus = 'Ap' THEN 'Approved'
                    WHEN t_Requisitions.DocStatus = 'AP' THEN 'Approved'
                    WHEN t_Requisitions.DocStatus = 'pe' THEN 'Pending'
                    WHEN t_Requisitions.DocStatus = 'Re' THEN 'Rejected'
                    WHEN t_Requisitions.DocStatus = 'RE' THEN 'Rejected'
                    ELSE t_CodeDetails.Description 
                END AS Status"),
                't_Requisitions.CreatedOn',
                DB::raw('isnull(t_Users.Name, t_Requisitions.CreatedBy) AS CreatedBy'),
                't_Requisitions.Id',
                DB::raw('SUM(ISNULL(t_RequisitionLines.Quantity, 0) * ISNULL(t_RequisitionLines.ExpectedPrice, 0)) AS ExpectedPrice'),
                DB::raw('COUNT(t_RequisitionLines.Id) AS itemcount'),
                DB::raw("t_ConsolidatedProcurementPlan.Title + ' - ' + t_ConsolidatedProcurementPlan.ReferenceNumber AS PlanTitle")
            ])
            ->groupBy(
                't_Requisitions.Id',
                't_Requisitions.RequisitionNo',
                't_Requisitions.BranchID',
                't_Requisitions.DepartmentID',
                't_Requisitions.Remarks',
                't_CodeDetails.Description',
                't_Requisitions.CreatedOn',
                't_Departments.Name',
                't_Branches.Name',
                't_ConsolidatedProcurementPlan.Title',
                't_ConsolidatedProcurementPlan.ReferenceNumber',
                't_Requisitions.CreatedBy',
                't_Users.Name',
                't_Requisitions.DocStatus'
            )
            ->first();
    }

    public static function fetchBranches()
    {
        return DB::table(DB::raw('t_Branches WITH (NOLOCK)'))
            ->select('Id', 'Name', 'BranchID')
            ->whereNull('DeletedBy')
            ->whereNull('DeletedOn')
            ->get();
    }


    public static function fetchDepartments()
    {
        return DB::table(DB::raw('t_Departments WITH (NOLOCK)'))
            ->select('Id', 'Name', 'DepartmentID')
            ->whereNull('DeletedBy')
            ->whereNull('DeletedOn')
            ->get();
    }

    public static function fetchProcurementPlan()
    {
        return DB::table(DB::raw('t_ConsolidatedProcurementPlan WITH (NOLOCK)'))
            ->select('PlanID', 'Title', 'ReferenceNumber')
            ->where('Status', '=', 'Ap')
            ->whereNull('DeletedBy')
            ->whereNull('DeletedOn')
            ->get();
    }
}
