<?php

namespace App\Services\Procurement\Requisition;

use App\Models\Procurement\Requisitions;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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
            \Log::error('SQL Error executing p_AddRequisition: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Return the error message back to the controller
            return response()->json([
                'message' => 'SQL Error executing requisition creation',
                'error' => $e->getMessage()
            ], 500);
        } catch (Throwable $e) {
            // Log the exception for debugging
            \Log::error('Error executing p_AddRequisition: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Return a custom error message or handle as needed
            return response()->json([
                'message' => 'Error executing requisition creation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
