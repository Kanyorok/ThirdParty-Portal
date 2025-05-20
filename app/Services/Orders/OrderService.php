<?php

namespace App\Services\Orders;

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

}
