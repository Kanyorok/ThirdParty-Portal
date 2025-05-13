<?php

namespace App\Services\Orders;

use App\Models\User;
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

    public static function addPO($supplier,$poDate,$rfqNo,$priority,$terms, User $actor)
    {
        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($supplier, $poDate, $rfqNo, $priority, $terms, $actor) {

                DB::statement('EXEC p_AddPurchaseOrder ?, ?, ?, ?, ?, ?', [
                    $supplier,
                    $poDate,
                    $rfqNo,
                    $priority,
                    $terms,
                    $actor->Id // Pass the User ID, not the entire User model
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
//

    public static function addPOLines($item,$quantity,$price,$tax,$discount,$linetotal,User $actor)
    {
        try {
            // Start transaction and execute the stored procedure
            DB::transaction(function () use ($item, $quantity, $price, $tax, $discount, $linetotal, $actor) {

                DB::statement('EXEC p_AddPurchaseOrderLines ?, ?, ?, ?, ?, ?', [
                    $item,
                    $quantity,
                    $price,
                    $tax,
                    $discount,
                    $linetotal,
                    $actor->Id // Pass the User ID, not the entire User model
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
