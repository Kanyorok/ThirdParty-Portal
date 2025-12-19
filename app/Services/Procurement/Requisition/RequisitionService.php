<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Auth\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\Procurement\Requisitions;

class RequisitionService
{
    public function __construct()
    {
        //
    }

public static function addRequisition($branch, $department, $remarks, $procurementPlanId, User $actor)
{
    try {
        $requisitionId = null;

        DB::transaction(function () use ($branch, $department, $remarks, $procurementPlanId, $actor, &$requisitionId) {
            // Execute stored procedure
            DB::statement('EXEC p_AddRequisition ?, ?, ?, ?, ?', [
                $branch,
                $department,
                $remarks,
                $procurementPlanId, // Pass plan ID instead of category
                $actor->Id
            ]);

            // Get the newly created requisition
            $requisition = Requisitions::where('CreatedBy', $actor->Id)
                ->orderBy('CreatedOn', 'desc')
                ->first();

            if ($requisition) {
                $requisitionId = $requisition->Id;
                
                // Ensure requisition starts in DRAFT status
                DB::table('t_Requisitions')
                    ->where('Id', $requisitionId)
                    ->update([
                        'DocStatus' => 'DR', // Draft status
                        'PlanRef' => $procurementPlanId, // Link to procurement plan
                        'ModifiedBy' => $actor->Id,
                        'ModifiedOn' => now()
                    ]);
                
                Log::info("Requisition created with Draft status", [
                    'requisition_id' => $requisitionId,
                    'plan_id' => $procurementPlanId,
                    'user_id' => $actor->Id
                ]);
                
                // Auto-populate items if procurement plan is selected
                if ($procurementPlanId) {
                    $itemsAdded = self::autoPopulateItemsFromPlan(
                        $requisitionId, 
                        $procurementPlanId, 
                        $actor
                    );
                    
                    Log::info("Auto-populated {$itemsAdded} items from plan", [
                        'requisition_id' => $requisitionId,
                        'plan_id' => $procurementPlanId
                    ]);
                }
            }
        });

        $message = $procurementPlanId 
            ? 'Requisition created successfully with items from procurement plan. Review and adjust as needed.'
            : 'Requisition created successfully. Please add items before submitting.';

        return [
            'status' => 'success',
            'message' => $message,
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

/**
 * Auto-populate requisition items from procurement plan
 * Uses PlanLineRef to track which items came from the plan
 */
private static function autoPopulateItemsFromPlan($requisitionId, $planId, User $actor)
{
    try {
        // Get draft status ID for requisition lines
        $draftStatusId = DB::table('t_CodeDetails')
            ->where('CodeId', 'RequisitionStatus')
            ->where('Description', 'Draft')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->value('ID');
        
        // Get medium urgency ID (default)
        $mediumUrgencyId = DB::table('t_CodeDetails')
            ->where('CodeId', 'UrgencyLevel')
            ->where('Value', '3')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->value('ID');

        // Get all line items from the procurement plan with full details
        $planItems = DB::table('t_PlanLineItem as pli')
            ->join('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
            ->leftJoin('t_ItemTypes as it', 'itm.TypeID', '=', 'it.Id')
            ->where('pli.PlanID', $planId)
            ->whereNull('pli.DeletedOn')
            ->select(
                'pli.LineItemID',
                'pli.ItemID',
                'itm.Name as ItemName',
                'pli.ItemDescription as Description',
                'pli.Quantity as PlanQuantity',
                'pli.UOMID',
                'pli.UnitPrice',
                'it.TypeName as ItemType'
            )
            ->get();

        if ($planItems->isEmpty()) {
            Log::warning("No items found in procurement plan {$planId}");
            return 0;
        }

        $itemsAdded = 0;
        
        foreach ($planItems as $item) {
            // Calculate remaining quantity (already used in other requisitions)
            $usedQty = DB::table('t_RequisitionLines')
                ->where('PlanLineRef', $item->LineItemID)
                ->whereNull('DeletedOn')
                ->sum('Quantity');
            
            $remainingQty = $item->PlanQuantity - $usedQty;
            
            // Only add if there's remaining quantity
            if ($remainingQty > 0) {
                // Get UOM name
                $uomName = DB::table('t_UnitOfMeasurement')
                    ->where('Id', $item->UOMID)
                    ->value('Name');
                
                // Insert directly into t_RequisitionLines
                // Note: We're NOT using the stored procedure here because we need specific control
                DB::table('t_RequisitionLines')->insert([
                    'Type' => $item->ItemType ?? 'General',
                    'Item' => $item->ItemID,
                    'Description' => $item->Description ?? $item->ItemName,
                    'UOM' => $uomName ?? 'Unit',
                    'Quantity' => $remainingQty,
                    'ExpectedPrice' => $item->UnitPrice ?? 0, // Unit price, not total
                    'UrgencyID' => $mediumUrgencyId ?? 1,
                    'StatusID' => $draftStatusId ?? 1,
                    'PlanLineRef' => $item->LineItemID, // Link to plan line - THIS IS KEY!
                    'RequisitionID' => $requisitionId,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]);
                
                $itemsAdded++;
            }
        }
        
        return $itemsAdded;
        
    } catch (\Exception $e) {
        Log::error("Failed to auto-populate items from plan: " . $e->getMessage(), [
            'plan_id' => $planId,
            'requisition_id' => $requisitionId,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Don't throw - let requisition be created even if items fail
        return 0;
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
                    WHEN UPPER(t_Requisitions.DocStatus) = \'AP\' THEN \'Approved\'
                    WHEN UPPER(t_Requisitions.DocStatus) = \'PE\' THEN \'Pending\'
                    WHEN UPPER(t_Requisitions.DocStatus) = \'RE\' THEN \'Rejected\'
                    WHEN UPPER(t_Requisitions.DocStatus) = \'DR\' THEN \'Draft\'
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
                    WHEN UPPER(t_Requisitions.DocStatus) = 'AP' THEN 'Approved'
                    WHEN UPPER(t_Requisitions.DocStatus) = 'PE' THEN 'Pending'
                    WHEN UPPER(t_Requisitions.DocStatus) = 'RE' THEN 'Rejected'
                    WHEN UPPER(t_Requisitions.DocStatus) = 'DR' THEN 'Draft'
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