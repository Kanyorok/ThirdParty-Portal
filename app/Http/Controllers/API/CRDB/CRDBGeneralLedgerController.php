<?php

namespace App\Http\Controllers\API\CRDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CRDBGeneralLedgerController extends Controller
{
    public function syncGeneralLedgers()
    {
// Add error handling and debugging
        try {
            // Check if the stored procedure exists and runs
            $data = DB::select("EXEC p_GLAccounts");
            
            // Check if data is empty
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
                'message' => 'General Ledger Fetched Successfully',
                'data' => collect($data)
            ], 200);
            
        } catch (\Exception $e) {
        
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to execute stored procedure: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function syncGLBalances()
    {
        try {
            $data = DB::select("EXEC p_GetGLBalances");
            // Check if data is empty
            if (empty($data)) {
                return response()->json([
                    'status' => 'empty',
                    'code' => 404,
                    'message' => 'Stored procedure executed but returned no data',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'count' => count($data),
                'message' => 'General Ledger Balances Fetched Successfully',
                'data' => collect($data)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to execute stored procedure: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}