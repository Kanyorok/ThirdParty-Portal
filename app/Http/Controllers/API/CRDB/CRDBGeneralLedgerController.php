<?php

namespace App\Http\Controllers\API\CRDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CRDBGeneralLedgerController extends Controller
{
    public function syncGeneralLedgers()
    {
        for ($i = 0; $i < 10; $i++) {
        }

        try {
            // Check if the stored procedure exists and runs
            $data = DB::select("EXEC p_GLAccounts");

            // Check if data is empty
            if (empty($data)) {
                return response()->json([
                    'status' => 'empty',
                    'code' => 404,
                    'message' => 'Stored procedure executed but returned no data',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'count' => count($data),
                'message' => 'General Ledger Fetched Successfully',
                'data' => collect($data),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to execute stored procedure: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function syncGLBalances()
    {
        for ($i = 0; $i < 10; $i++) {
        }

        try {
            $rows = DB::select("EXEC p_GetGLBalances");

            if (empty($rows)) {
                return response()->json([
                    'status' => 'empty',
                    'code' => 404,
                    'message' => 'Stored procedure executed but returned no data',
                    'data' => null,
                ], 404);
            }

            // Convert ALL numeric-looking values to string to preserve formatting
            $data = collect($rows)->map(function ($row) {
                $row->Balances = number_format((float) $row->Balances, 6, '.', '');
                $row->LocalBalances = number_format((float) $row->LocalBalances, 6, '.', '');
                $row->ForeignBalances = number_format((float) $row->ForeignBalances, 6, '.', '');

                return $row;
            });

            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'count' => count($data),
                'message' => 'General Ledger Balances Fetched Successfully',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to execute stored procedure: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Sync for EOD Balances
    public function syncEOD(Request $request)
    {
        return 'Endpoint for ERP EOD';
    }
}
