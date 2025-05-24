<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Auth\User;
use App\Models\Procurement\RequisitionLines;
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

    public static function create(array $data, User $actor): RequisitionLines
    {
        return  RequisitionLines::create([
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


    public static function addRequisitionLines($RequisitionId,$Item,$Quantity,$Urgency,User $actor): array
    {

        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($RequisitionId,$Item,$Quantity,$Urgency,$actor) {

                DB::statement('EXEC p_AddRequisitionLines ?, ?, ?, ?, ?', [
                    $RequisitionId,$Item,$Quantity,$Urgency,
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
            return[
                'status' => 'error',
                'message' => 'Error executing requisitionlines creation',
                'error' => $e->getMessage()
            ];
        }
    }
//


    public static function getRequisitionItems(){

        return DB::table(DB::raw('t_RequisitionLines WITH (NOLOCK)'))
            ->Join(DB::raw('t_Requisitions WITH (NOLOCK)'), 't_RequisitionLines.RequisitionID', '=', 't_Requisitions.Id')
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_Items.Id', '=', 't_RequisitionLines.Item')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Users.Id', '=', 't_RequisitionLines.CreatedBy')
            ->leftJoin(DB::raw('t_ItemCategories WITH (NOLOCK)'), 't_Items.CategoryId', '=', 't_ItemCategories.id')
            ->select(
                't_Items.Name as Item',
                't_Users.Name as UserName',
                't_Items.UOM as UOMx',
                't_ItemCategories.Name as Category',
                't_Requisitions.RequisitionNo',
                't_Requisitions.BranchID',
                't_Requisitions.DepartmentID',
                't_Items.UnitPrice',
                DB::raw('t_RequisitionLines.ExpectedPrice * t_RequisitionLines.Quantity as ExpectedPrice'),
                DB::raw('t_Items.UnitPrice * t_RequisitionLines.Quantity as ActualPrice'),
                DB::raw("CASE
            WHEN t_RequisitionLines.Urgency = 1 THEN 'Very High'
            WHEN t_RequisitionLines.Urgency = 2 THEN 'High'
            WHEN t_RequisitionLines.Urgency = 3 THEN 'Medium'
            WHEN t_RequisitionLines.Urgency = 4 THEN 'Low'
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

    public static function getRequisitionRelatedItems($RequsitionId){

        return DB::table(DB::raw('t_RequisitionLines WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_RequisitionLines.Item', '=', 't_Items.Id')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_RequisitionLines.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_ItemCategories WITH (NOLOCK)'), 't_Items.Category', '=', 't_ItemCategories.Id')
            ->where('t_RequisitionLines.RequisitionId',$RequsitionId)
            ->select(
                't_RequisitionLines.*',
                't_Items.ItemName as ItemName',
                't_Users.Name as UserName',
                't_Items.UOM as UOMx',
                't_ItemCategories.Name as Category',
                DB::raw('t_RequisitionLines.ExpectedPrice * t_RequisitionLines.Quantity as ExpectedPrice'),
                DB::raw('0 * t_RequisitionLines.Quantity as ActualPrice'),
                'StatusID as Status',
                'UrgencyID as Urgency',
                DB::raw("FORMAT(t_RequisitionLines.CreatedOn, 'dd-MM-yyyy HH:mm') as CreatedOn")
            )
            ->get();
    }



}
