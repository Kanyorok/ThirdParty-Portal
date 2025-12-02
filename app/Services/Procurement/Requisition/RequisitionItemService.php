<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Auth\User;
use App\Models\Procurement\RequisitionLine;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RequisitionItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function create(array $data, User $actor): RequisitionLine
    {
        return RequisitionLine::create([
            'Module' => $data['Module'],
            'Type' => $data['Type'],
            'Item' => $data['Item'],
            'Description' => $data['Description'],
            'Quantity' => $data['Quantity'],
            'UOM' => $data['UOM'],
            'ExpectedPrice' => $data['ExpectedPrice'],
            'Urgency' => $data['Urgency'],
            'CategoryId' => $data['CategoryId'],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id
            // optionally CreatedBy etc.
        ]);
    }


    public static function addRequisitionLines($RequisitionId, $Item, $Quantity, $Urgency, $UOM, $EstimatedPrice, $LineItemID, User $actor): array
    {

        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($RequisitionId, $Item, $Quantity, $Urgency, $UOM, $EstimatedPrice, $LineItemID, $actor) {

                DB::statement('EXEC p_AddRequisitionLines ?, ?, ?, ?, ?, ?, ?, ?', [
                    $RequisitionId,
                    $Item,
                    $Quantity,
                    $Urgency,
                    $UOM,
                    $EstimatedPrice,
                    $LineItemID,
                    $actor->Id // Pass the User ID, not the entire User model
                ]);
            });

            return [
                'status' => 'success',
                'message' => 'RequisitionLines successfully created.'
            ];
        } catch (QueryException $e) {
            // Log the SQL error
            Log::error('SQL Error executing p_AddRequisitionLines', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return the error message back to the controller
            return [
                'status' => 'error',
                'message' => 'SQL error executing requisitionlines creation',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            // Log the exception for debugging
            Log::error('Error executing p_AddRequisitionlines', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return a custom error message or handle as needed
            return [
                'status' => 'error',
                'message' => 'Error executing requisitionlines creation',
                'error' => $e->getMessage()
            ];
        }
    }
    //


    public static function getRequisitionItems()
    {

        return DB::table(DB::raw('t_RequisitionLines WITH (NOLOCK)'))
            ->Join(DB::raw('t_Requisitions WITH (NOLOCK)'), 't_RequisitionLines.RequisitionID', '=', 't_Requisitions.Id')
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_Items.Id', '=', 't_RequisitionLines.Item')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Users.Id', '=', 't_RequisitionLines.CreatedBy')
            ->leftJoin(DB::raw('t_ItemCategories WITH (NOLOCK)'), 't_Items.CategoryId', '=', 't_ItemCategories.id')
            ->leftJoin(DB::raw('t_uom WITH (NOLOCK)'), 't_Items.UOM', '=', 't_uom.Id')
            ->select(
                't_Items.Name as Item',
                't_Users.Name as UserName',
                't_uom.Code as UOMx',
                't_ItemCategories.Name as Category',
                't_Requisitions.RequisitionNo',
                't_Requisitions.BranchID',
                't_Requisitions.DepartmentID',
                't_Items.UnitPrice',
                DB::raw('t_RequisitionLines.ExpectedPrice * t_RequisitionLines.Quantity as ExpectedPrice'),
                DB::raw('t_Items.UnitPrice * t_RequisitionLines.Quantity as ActualPrice'),
                DB::raw("CASE
            WHEN t_RequisitionLines.UrgencyID = 1 THEN 'Very High'
            WHEN t_RequisitionLines.UrgencyID = 2 THEN 'High'
            WHEN t_RequisitionLines.UrgencyID = 3 THEN 'Medium'
            WHEN t_RequisitionLines.UrgencyID = 4 THEN 'Low'
            ELSE 'Unknown'
        END as Urgency"),
                DB::raw("FORMAT(t_RequisitionLines.CreatedOn, 'dd-MM-yyyy') as CreatedOn"),
                DB::raw(" t_Requisitions.RequisitionNo,
                t_Requisitions.BranchID,
                t_Requisitions.DepartmentID,
                t_Requisitions.Remarks,
                t_Requisitions.Category,
                t_Requisitions.Id,
                FORMAT(t_RequisitionLines.NeededBy, 'dd-MM-yyyy') as NeededBy,
                case when t_RequisitionLines.StatusID = 'p' then 'Pending' when t_RequisitionLines.StatusID = 'a'
                then 'Approved' when t_RequisitionLines.StatusID = 'r' then 'Rejected'
                when t_RequisitionLines.StatusID = 'd' then 'Deferred'
                else  'Unknown' end as Status,
                t_RequisitionLines.ExpectedPrice,
                t_RequisitionLines.Quantity"),
            )->orderByRaw('t_RequisitionLines.Urgency, t_RequisitionLines.NeededBy ASC')
            ->get();
    }

    /**
     * Returns a list of requisitions ranked by aggregated urgency of their lines.
     * Score = VeryHigh*4 + High*3 + Medium*2 + Low*1
     */
    public static function getRequisitionPriorityList()
    {
        return DB::table(DB::raw('t_RequisitionLines rl WITH (NOLOCK)'))
            ->join(DB::raw('t_Requisitions r WITH (NOLOCK)'), 'rl.RequisitionID', '=', 'r.Id')
            ->leftJoin(DB::raw('t_Users u WITH (NOLOCK)'), 'r.CreatedBy', '=', 'u.Id')
            ->select(
                'r.Id',
                'r.RequisitionNo',
                DB::raw("FORMAT(r.CreatedOn, 'dd-MM-yyyy') as RequisitionDate"),
                'r.BranchID',
                'r.DepartmentID',
                'u.Name as RequestedBy',
                DB::raw('COUNT(*) as TotalItems'),
                DB::raw('SUM(CASE WHEN rl.UrgencyID = 1 THEN 1 ELSE 0 END) as VeryHighCount'),
                DB::raw('SUM(CASE WHEN rl.UrgencyID = 2 THEN 1 ELSE 0 END) as HighCount'),
                DB::raw('SUM(CASE WHEN rl.UrgencyID = 3 THEN 1 ELSE 0 END) as MediumCount'),
                DB::raw('SUM(CASE WHEN rl.UrgencyID = 4 THEN 1 ELSE 0 END) as LowCount'),
                // Weighted score: very high=4, high=3, medium=2, low=1
                DB::raw('(SUM(CASE WHEN rl.UrgencyID = 1 THEN 4 WHEN rl.UrgencyID = 2 THEN 3 WHEN rl.UrgencyID = 3 THEN 2 WHEN rl.UrgencyID = 4 THEN 1 ELSE 0 END)) as Score')
            )
            ->groupBy('r.Id', 'r.RequisitionNo', 'r.CreatedOn', 'r.BranchID', 'r.DepartmentID', 'u.Name')
            ->orderByDesc('Score')
            ->orderBy('r.CreatedOn', 'desc')
            ->get();
    }

    public static function getRequisitionRelatedItems($RequisitionId)
    {
        return DB::table(DB::raw('t_RequisitionLines WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_RequisitionLines.Item', '=', 't_Items.Id')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_RequisitionLines.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_ItemCategories WITH (NOLOCK)'), 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin(DB::raw('t_uom WITH (NOLOCK)'), 't_Items.UOM', '=', 't_uom.Id')
            ->leftJoin(DB::raw('t_ItemTypes WITH (NOLOCK)'), 't_Items.ItemType', '=', 't_ItemTypes.TypeName')
            ->leftJoin(DB::raw('t_CodeDetails WITH (NOLOCK)'), 't_ItemTypes.TypeName', '=', 't_CodeDetails.Id')
            ->where('t_RequisitionLines.RequisitionId', $RequisitionId)
            ->select([
                't_RequisitionLines.Id',
                't_RequisitionLines.Quantity',
                't_Items.ItemName as ItemName',
                't_Items.ItemDescription as Description',
                't_Users.Name as UserName',
                't_uom.Code as UOM',
                't_CodeDetails.Description as Type',
                't_ItemCategories.Name as Category',
                // Need ID from the approved plan (if this line came from a plan). N/A otherwise
                DB::raw("ISNULL((SELECT TOP 1 dn.NeedID\n                              FROM t_PlanLineItem pli WITH (NOLOCK)\n                              JOIN t_DepartmentNeeds dn WITH (NOLOCK)\n                                ON dn.ItemID = pli.ItemID\n                               AND dn.BranchID = pli.BranchID\n                               AND dn.DepartmentID = pli.DepartmentID\n                             WHERE pli.LineItemID = t_RequisitionLines.PlanLineRef), 'N/A') as NeedRef"),
                // Per-unit expected price captured at line creation time (sourced from approved plan)
                't_RequisitionLines.ExpectedPrice as UnitPrice',
                DB::raw('t_RequisitionLines.ExpectedPrice * t_RequisitionLines.Quantity as ExpectedPrice'),
                't_RequisitionLines.StatusID as Status',
                DB::raw("CASE
            WHEN t_RequisitionLines.UrgencyID = 1 THEN 'Very High'
            WHEN t_RequisitionLines.UrgencyID = 2 THEN 'High'
            WHEN t_RequisitionLines.UrgencyID = 3 THEN 'Medium'
            WHEN t_RequisitionLines.UrgencyID = 4 THEN 'Low'
            ELSE 'Unknown'
        END as Urgency"),
                DB::raw("FORMAT(t_RequisitionLines.CreatedOn, 'dd-MM-yyyy HH:mm') as CreatedOn")
            ])
            ->get();
    }
}
