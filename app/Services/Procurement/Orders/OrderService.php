<?php

namespace App\Services\Procurement\Orders;

use App\Models\Auth\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function addPO($supplier, $poDate, $rfqNo, $priority, $terms, User $actor)
    {
        try {
            $response = DB::transaction(function () use ($supplier, $poDate, $rfqNo, $priority, $terms, $actor) {
                // Execute the stored procedure and capture the result
                $result = DB::select('EXEC p_AddPurchaseOrder ?, ?, ?, ?, ?, ?', [
                    $supplier,
                    $poDate,
                    $rfqNo,
                    $priority,
                    $terms,
                    $actor->Id // use lowercase `id`, Laravel convention
                ]);

                \Log::info('p_AddPurchaseOrder result', ['result' => $result]);

                $poId = $result[0]->POID ?? null;

                if (!$poId) {
                    throw new \Exception('Stored procedure executed but PO ID was not returned.');
                }

                return [
                    'status' => 'success',
                    'message' => 'Order successfully created.',
                    'po_id' => $poId,
                ];
            });

            return $response;

        } catch (QueryException $e) {
            Log::error('SQL Error executing p_AddPurchaseOrder', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            return [
                'status' => 'error',
                'message' => 'SQL error executing order creation',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            Log::error('Error executing p_AddPurchaseOrder', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            return [
                'status' => 'error',
                'message' => 'Error executing order creation',
                'error' => $e->getMessage()
            ];
        }
    }


//

    public static function addPOLines($item,$quantity,$price,$tax,$discount,$linetotal,User $actor, $orderId)
    {
        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($item, $quantity, $price, $tax, $discount, $linetotal, $actor, $orderId) {

                DB::statement('EXEC p_AddPurchaseOrderLines ?, ?, ?, ?, ?, ?, ?, ?', [
                    $item,
                    $quantity,
                    $price,
                    $tax,
                    $discount,
                    $linetotal,
                    $actor->Id ,// Pass the User ID, not the entire User model
                    $orderId
                ]);
            });

            return [
                'status' => 'success',
                'message' => 'Order successfully created.'
            ];

        } catch (QueryException $e) {
            // Log the SQL error
            Log::error('SQL Error executing p_AddPurchaseOrder', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return the error message back to the controller
            return [
                'status' => 'error',
                'message' => 'SQL error executing order creation',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            // Log the exception for debugging
            Log::error('Error executing p_AddPurchaseOrder', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return a custom error message or handle as needed
            return[
                'status' => 'error',
                'message' => 'Error executing order creation',
                'error' => $e->getMessage()
            ];
        }
    }


    public static function fetchOrders()
    {
        return DB::table(DB::raw('t_Orders WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_OrderLines WITH (NOLOCK)'), 't_Orders.Id', '=', 't_OrderLines.iOrderID')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_RFQ WITH (NOLOCK)'), 't_Orders.ExtOrdNum', '=', 't_RFQ.Id')
            ->select(DB::raw('
                t_Orders.Id,
                t_Orders.OrderDate,
                t_Orders.OrderNo,
                COALESCE(t_RFQ.RFQNumber,t_Orders.ExtOrdNum) as ExtOrdNum,
                t_Orders.Priority,
                t_Orders.CreatedOn,
                t_Users.Name as CreatedBy,
                t_Orders.BranchID,
                SUM(isnull(t_OrderLines.fUnitPriceExcl,0)) as UnitPrice,
                COUNT(t_OrderLines.Id) as ordercount,
                t_Orders.OrdTotExcl,
                t_Orders.OrdTotIncl,
                t_Orders.OrdTotTax,
                t_Orders.OrdDiscAmnt
            '))
            ->groupBy(
                't_Orders.Id',
                't_Orders.OrderDate',
                't_Orders.OrderNo',
                't_Orders.ExtOrdNum',
                't_Orders.Priority',
                't_Orders.CreatedOn',
                't_Users.Name',
                't_Orders.BranchID',
                't_Orders.OrdTotExcl',
                't_Orders.OrdTotIncl',
                't_Orders.OrdTotTax',
                't_Orders.OrdDiscAmnt',
                't_RFQ.RFQNumber'
            )
            ->get();
    }


    public static function fetchOrderDetails($id)
    {
        return DB::table(DB::raw('t_Orders WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_OrderLines WITH (NOLOCK)'), 't_Orders.Id', '=', 't_OrderLines.iOrderID')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_Suppliers WITH (NOLOCK)'), 't_Orders.AccountID', '=', 't_Suppliers.Id')
            ->leftJoin(DB::raw('t_RFQ WITH (NOLOCK)'), 't_Orders.ExtOrdNum', '=', 't_RFQ.Id')
            ->where('t_Orders.Id', '=', $id)
            ->select(DB::raw('
                t_Orders.Id,
                t_Orders.OrderDate,
                t_Orders.OrderNo,
                COALESCE(t_RFQ.RFQNumber,t_Orders.ExtOrdNum) as ExtOrdNum,
                t_Orders.Priority,
                t_Orders.CreatedOn,
                t_Users.Name as CreatedBy,
                t_Orders.BranchID,
                SUM(isnull(t_OrderLines.fUnitPriceExcl,0)) as UnitPrice,
                COUNT(t_OrderLines.Id) as ordercount,
                t_Orders.AccountID,
                t_Orders.OrdTotExcl,
                t_Orders.OrdTotIncl,
                t_Orders.OrdTotTax,
                t_Orders.OrdDiscAmnt,
                t_Suppliers.SupplierName
            '))
            ->groupBy(
                't_Orders.Id',
                't_Orders.OrderDate',
                't_Orders.OrderNo',
                't_Orders.ExtOrdNum',
                't_Orders.Priority',
                't_Orders.CreatedOn',
                't_Users.Name',
                't_Orders.BranchID',
                't_Orders.AccountID',
                't_Orders.OrdTotExcl',
                't_Orders.OrdTotIncl',
                't_Orders.OrdTotTax',
                't_Orders.OrdDiscAmnt',
                't_Suppliers.SupplierName',
                't_RFQ.RFQNumber'
            )
            ->first();
    }


    public static function fetchOrderLineDetails($id)
    {
        return DB::table(DB::raw('t_OrderLines WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_Orders WITH (NOLOCK)'), 't_OrderLines.iOrderID', '=', 't_Orders.Id')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_OrderLines.iStockCodeID', '=', 't_Items.Id')
            ->leftJoin(DB::raw('t_ItemCategories WITH (NOLOCK)'), 't_Items.Category', '=', 't_ItemCategories.Id')
            ->where('t_OrderLines.iOrderID', '=', $id)
            ->select(DB::raw('
                t_OrderLines.Id,
                t_OrderLines.iOrderID,
                t_OrderLines.fQuantity,
                t_OrderLines.fLineDiscount,
                t_OrderLines.fUnitPriceExcl,
                t_OrderLines.CreatedOn,
                t_Users.Name as CreatedBy,
                t_OrderLines.fTaxRate,
                t_Items.Id as ItemID,
                t_Items.ItemName,
                t_Items.ItemType,
                t_Items.ItemDescription as Description,
                t_OrderLines.LineTotal
            '))
            ->get();
    }

    public static function AddPurchaseOrderSum($orderId)
    {
        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($orderId) {

                DB::statement('EXEC p_AddPurchaseOrderSum ?', [
                    $orderId
                ]);
            });

            return [
                'status' => 'success',
                'message' => 'Order successfully updated.'
            ];

        } catch (QueryException $e) {
            // Log the SQL error
            Log::error('SQL Error executing p_AddPurchaseOrderSum', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return the error message back to the controller
            return [
                'status' => 'error',
                'message' => 'SQL error executing order update',
                'error' => $e->getMessage()
            ];
        } catch (Throwable $e) {
            // Log the exception for debugging
            Log::error('Error executing p_AddPurchaseOrderSum', [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);

            // Return a custom error message or handle as needed
            return [
                'status' => 'error',
                'message' => 'Error executing order update',
                'error' => $e->getMessage()
            ];
        }
    }


}
