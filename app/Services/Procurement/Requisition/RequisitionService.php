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
        Log::info("=== AUTO-POPULATE START ===", [
            'requisition_id' => $requisitionId,
            'plan_id' => $planId,
            'user_id' => $actor->Id
        ]);

        // Check if plan exists
        $planExists = DB::table('t_ConsolidatedProcurementPlan')
            ->where('PlanID', $planId)
            ->whereNull('DeletedOn')
            ->exists();
        
        if (!$planExists) {
            Log::warning("Plan does not exist", ['plan_id' => $planId]);
            return 0;
        }

        // Check plan line items
        $planLineCount = DB::table('t_PlanLineItem')
            ->where('PlanID', $planId)
            ->whereNull('DeletedOn')
            ->count();
        
        Log::info("Plan line items count", ['plan_id' => $planId, 'count' => $planLineCount]);

        if ($planLineCount === 0) {
            Log::warning("No line items found in plan", ['plan_id' => $planId]);
            return 0;
        }

        // Get a valid status for requisition lines
        // Try multiple status codes in order of preference
        $statusCodes = ['Su', 'PE', 'DR']; // Submitted, Pending, Draft
        $statusId = null;
        
        foreach ($statusCodes as $code) {
            $statusId = DB::table('t_CodeDetails')
                ->where('CodeID', 'RequisitionStatus')
                ->where('Value', $code)
                ->where('IsActive', 1)
                ->whereNull('DeletedOn')
                ->value('ID');
            
            if ($statusId) {
                Log::info("Found status", ['code' => $code, 'id' => $statusId]);
                break;
            }
        }
        
        // If no status found, use NULL (will rely on default)
        if (!$statusId) {
            Log::warning("No suitable status found, using NULL");
        }
        
        // Get urgency - if not available, use NULL
        $urgencyId = DB::table('t_CodeDetails')
            ->where('CodeID', 'UrgencyLevel')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->orderBy('ID')
            ->value('ID');

        if (!$urgencyId) {
            Log::info("No urgency level found - will use NULL");
        }

        // FIXED: Properly join to get item type information
        $planItems = DB::table('t_PlanLineItem as pli')
            ->leftJoin('t_Items as itm', 'pli.ItemID', '=', 'itm.Id')
            ->leftJoin('t_ItemTypes as it', 'itm.ItemType', '=', 'it.Id')
            ->where('pli.PlanID', $planId)
            ->whereNull('pli.DeletedOn')
            ->select(
                'pli.LineItemID',
                'pli.ItemID',
                // 'pli.ItemDescription as PlanDescription', // Column does not exist
                'pli.Quantity as PlanQuantity',
                'pli.UOMID',
                'pli.UnitPrice',
                'itm.ItemName',
                'itm.ItemDescription as ItemDescription',
                'itm.ItemType as ItemTypeId',
                'it.TypeName as ItemTypeCode' // This is actually an ID reference
            )
            ->get();

        Log::info("Plan items fetched", [
            'count' => $planItems->count(),
            'sample' => $planItems->first() ? json_encode($planItems->first()) : null
        ]);

        if ($planItems->isEmpty()) {
            Log::error("No items found in plan after join - check table structure");
            
            // Debug: Check what's actually in the plan
            $rawPlanItems = DB::table('t_PlanLineItem')
                ->where('PlanID', $planId)
                ->whereNull('DeletedOn')
                ->get();
            
            Log::info("Raw plan items (without joins)", [
                'count' => $rawPlanItems->count(),
                'sample' => $rawPlanItems->first() ? json_encode($rawPlanItems->first()) : null
            ]);
            
            return 0;
        }

        $itemsAdded = 0;
        $errors = [];
        
        foreach ($planItems as $item) {
            try {
                Log::info("Processing plan item", [
                    'line_item_id' => $item->LineItemID,
                    'item_id' => $item->ItemID,
                    'plan_quantity' => $item->PlanQuantity
                ]);

                // Calculate remaining quantity
                $usedQty = DB::table('t_RequisitionLines')
                    ->where('PlanLineRef', $item->LineItemID)
                    ->whereNull('DeletedOn')
                    ->sum('Quantity') ?? 0;
                
                $remainingQty = $item->PlanQuantity - $usedQty;
                
                Log::info("Quantity calculation", [
                    'plan_qty' => $item->PlanQuantity,
                    'used_qty' => $usedQty,
                    'remaining_qty' => $remainingQty
                ]);
                
                if ($remainingQty <= 0) {
                    Log::info("Skipping - no remaining quantity", ['line_item_id' => $item->LineItemID]);
                    continue;
                }

                // Get UOM - try to get the actual UOM code
                $uomName = 'Unit'; // Default
                if (!empty($item->UOMID)) {
                    $uom = DB::table('t_UOM')
                        ->where('Id', $item->UOMID)
                        ->first();
                    
                    if ($uom) {
                        $uomName = $uom->Code ?? $uom->Name ?? $uomName;
                        Log::info("UOM lookup", ['uom_id' => $item->UOMID, 'uom' => $uomName]);
                    } else {
                        Log::warning("UOM not found", ['uom_id' => $item->UOMID]);
                    }
                }
                
                // Prepare description - use the most descriptive available
                $description = trim($item->PlanDescription ?? $item->ItemDescription ?? $item->ItemName ?? 'Item');
                
                // Prepare insert data with all required fields
                $insertData = [
                    'RequisitionID' => $requisitionId,
                    'Item' => $item->ItemID,
                    'Description' => $description,
                    'UOM' => $uomName,
                    'Quantity' => $remainingQty,
                    'ExpectedPrice' => $item->UnitPrice ?? 0,
                    'PlanLineRef' => $item->LineItemID,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ];
                
                // Add optional fields only if they have values
                if ($statusId) {
                    $insertData['StatusID'] = $statusId;
                }
                
                if ($urgencyId) {
                    $insertData['UrgencyID'] = $urgencyId;
                }
                
                if (!empty($item->ItemTypeId)) {
                    $insertData['Type'] = $item->ItemTypeId;
                }
                
                Log::info("Attempting to insert requisition line", [
                    'data' => $insertData
                ]);
                
                DB::table('t_RequisitionLines')->insert($insertData);
                
                $itemsAdded++;
                
                Log::info("Item added successfully", [
                    'items_added' => $itemsAdded,
                    'line_item_id' => $item->LineItemID
                ]);
                
            } catch (\Exception $itemError) {
                $errorMsg = "Failed to insert line item {$item->LineItemID}: {$itemError->getMessage()}";
                Log::error($errorMsg, [
                    'item' => $item,
                    'insert_data' => $insertData ?? null,
                    'trace' => $itemError->getTraceAsString()
                ]);
                $errors[] = $errorMsg;
            }
        }
        
        if (!empty($errors)) {
            Log::warning("Some items failed to import", ['errors' => $errors]);
        }
        
        Log::info("=== AUTO-POPULATE COMPLETE ===", [
            'total_items_added' => $itemsAdded,
            'total_errors' => count($errors),
            'requisition_id' => $requisitionId,
            'plan_id' => $planId
        ]);
        
        return $itemsAdded;
        
    } catch (\Exception $e) {
        Log::error("=== AUTO-POPULATE FAILED ===", [
            'plan_id' => $planId,
            'requisition_id' => $requisitionId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
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
                END AS PlanTitle"),
                't_Requisitions.PlanRef'
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
                't_ConsolidatedProcurementPlan.ReferenceNumber',
                't_Requisitions.CreatedBy',
                't_Users.Name',
                't_Requisitions.DocStatus',
                't_Requisitions.PlanRef'
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
        // 1. Get the ID for 'RFQ' procurement method
        // We look for 'RFQ' in CodeDetails where CodeID is ProcurementMethod
        $rfqMethodId = DB::table('t_CodeDetails')
            ->where('CodeID', 'ProcurementMethod')
            ->where(function($q) {
                $q->where('Value', 'RFQ')
                  ->orWhere('Description', 'RFQ');
            })
            ->value('ID');

        $query = DB::table(DB::raw('t_ConsolidatedProcurementPlan WITH (NOLOCK)'))
            ->select('PlanID', 'Title', 'ReferenceNumber')
            ->where(function($q) {
                $q->where('Status', '=', 'Ap')
                  ->orWhere('Status', '=', 'Approved'); // Handle both cases just to be safe
            })
            ->whereNull('DeletedBy')
            ->whereNull('DeletedOn');

        // 2. Filter by having at least one RFQ line item
        if ($rfqMethodId) {
            $query->whereExists(function ($subquery) use ($rfqMethodId) {
                $subquery->select(DB::raw(1))
                    ->from('t_PlanLineItem')
                    ->whereColumn('t_PlanLineItem.PlanID', 't_ConsolidatedProcurementPlan.PlanID')
                    ->where('t_PlanLineItem.ProcurementMethod', $rfqMethodId)
                    ->whereNull('t_PlanLineItem.DeletedOn');
            });
        }

        return $query->get();
    }
}