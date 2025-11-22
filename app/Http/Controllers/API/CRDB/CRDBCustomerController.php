<?php

namespace App\Http\Controllers\API\CRDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CRDBCustomerController extends Controller
{
    //Sync customer info by executing the p_Customers stored procedure EXEC dbo.r_CustomerData  @IsSynced = 0

    public function syncCustomers(Request $request)
    {
        $request->validate([
            'IsSynced' => 'required|boolean',
        ]);
        try {
            $data = DB::select("EXEC dbo.r_CustomerData @IsSynced = 0");
            if (empty($data)) {
                return response()->json([
                    'status' => 'empty',
                    'code' => 404,
                    'message' => 'Stored procedure executed but returned no data',
                    'data' => []
                ], 404);
            }
            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'count' => count($data),
                'message' => 'Customer Data Fetched Successfully',
                'data' => collect($data)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to sync customer data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }   
}
