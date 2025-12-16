<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Auth\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Procurement\Requisitions;
use App\Services\Workflow\ApprovalWorkflow;
use App\Enums\WorfklowStatus; // You'll need this enum
use App\Enums\WorkflowStatus;

class RequisitionService
{
    public function __construct()
    {
        //
    }

    public static function addRequisition($branch, $department, $remarks, $category, User $actor)
    {
        try {
            $requisitionId = null;

            DB::transaction(function () use ($branch, $department, $remarks, $category, $actor, &$requisitionId) {
                // Execute stored procedure
                DB::statement('EXEC p_AddRequisition ?, ?, ?, ?, ?', [
                    $branch,
                    $department,
                    $remarks,
                    $category,
                    $actor->Id
                ]);

                // Get the newly created requisition
                $requisition = Requisitions::where('CreatedBy', $actor->Id)
                    ->orderBy('CreatedOn', 'desc')
                    ->first();

                if ($requisition) {
                    $requisitionId = $requisition->Id;
                
                }
            });

            return [
                'status' => 'success',
                'message' => 'Requisition successfully created and please add items to submit.',
                'requisition_id' => $requisitionId
            ];
        } catch (QueryException $e) {
            Log::error('SQL Error executing p_AddRequisition', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            return [
                'status' => 'error',
                'message' => 'Database error creating requisition',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            Log::error('Error executing p_AddRequisition', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            return [
                'status' => 'error',
                'message' => 'Error creating requisition',
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
            return collect();
        }
    }

    public static function fetchRequisition()
    {
        return DB::table(DB::raw('t_Requisitions WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_RequisitionLines WITH (NOLOCK)'), 't_Requisitions.Id', '=', 't_RequisitionLines.RequisitionId')
            ->leftJoin(DB::raw('t_CodeDetails WITH (NOLOCK)'), 't_Requisitions.StatusID', '=', 't_CodeDetails.ID')
            ->leftJoin(DB::raw('t_Branches WITH (NOLOCK)'), 't_Requisitions.BranchID', '=', 't_Branches.Id')
            ->leftJoin(DB::raw('t_Departments WITH (NOLOCK)'), 't_Requisitions.DepartmentID', '=', 't_Departments.Id')
            ->leftJoin(DB::raw('t_ConsolidatedProcurementPlan WITH (NOLOCK)'), 't_Requisitions.PlanRef', '=', 't_ConsolidatedProcurementPlan.PlanID')
            ->select(DB::raw('
                t_Requisitions.Id,
                t_Requisitions.RequisitionNo,
                COALESCE(t_Branches.Name, t_Requisitions.BranchID) as BranchID,
                COALESCE(t_Departments.Name, t_Requisitions.DepartmentID) as DepartmentID,
                t_Requisitions.Remarks,
                CASE 
                    WHEN t_Requisitions.DocStatus = \'Ap\' THEN \'Approved\'
                    WHEN t_Requisitions.DocStatus = \'AP\' THEN \'Approved\'
                    WHEN t_Requisitions.DocStatus = \'pe\' THEN \'Pending\'
                    WHEN t_Requisitions.DocStatus = \'PE\' THEN \'Pending\'
                    WHEN t_Requisitions.DocStatus = \'Re\' THEN \'Rejected\'
                    WHEN t_Requisitions.DocStatus = \'RE\' THEN \'Rejected\'
                    WHEN t_CodeDetails.Description IS NOT NULL THEN t_CodeDetails.Description
                    ELSE \'Draft\'
                END as Status,
                t_Requisitions.CreatedOn,
                SUM(ISNULL(t_RequisitionLines.Quantity, 0) * ISNULL(t_RequisitionLines.ExpectedPrice, 0)) as ExpectedPrice,
                COUNT(CASE WHEN t_RequisitionLines.Id IS NOT NULL THEN 1 END) as itemcount,
                CASE 
                    WHEN t_ConsolidatedProcurementPlan.PlanID IS NOT NULL 
                    THEN t_ConsolidatedProcurementPlan.Title + \' - \' + t_ConsolidatedProcurementPlan.ReferenceNumber
                    ELSE NULL
                END as PlanTitle
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
                't_ConsolidatedProcurementPlan.PlanID',
                't_ConsolidatedProcurementPlan.Title',
                't_ConsolidatedProcurementPlan.ReferenceNumber',
                't_Requisitions.DocStatus'
            )
            ->orderBy('t_Requisitions.CreatedOn', 'DESC')
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
                    WHEN t_Requisitions.DocStatus = 'PE' THEN 'Pending'
                    WHEN t_Requisitions.DocStatus = 'Re' THEN 'Rejected'
                    WHEN t_Requisitions.DocStatus = 'RE' THEN 'Rejected'
                    ELSE COALESCE(t_CodeDetails.Description, 'Draft')
                END AS Status"),
                't_Requisitions.CreatedOn',
                DB::raw('ISNULL(t_Users.Name, t_Requisitions.CreatedBy) AS CreatedBy'),
                't_Requisitions.Id',
                DB::raw('SUM(ISNULL(t_RequisitionLines.Quantity, 0) * ISNULL(t_RequisitionLines.ExpectedPrice, 0)) AS ExpectedPrice'),
                DB::raw('COUNT(t_RequisitionLines.Id) AS itemcount'),
                DB::raw("CASE 
                    WHEN t_ConsolidatedProcurementPlan.PlanID IS NOT NULL 
                    THEN t_ConsolidatedProcurementPlan.Title + ' - ' + t_ConsolidatedProcurementPlan.ReferenceNumber
                    ELSE NULL
                END AS PlanTitle")
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
                't_ConsolidatedProcurementPlan.PlanID',
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