<?php

namespace App\Http\Controllers\API\CRDB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CRDBCustomerController extends Controller
{
    // Sync customer info by executing the p_Customers stored procedure EXEC dbo.r_CustomerData @IsSynced = 0

    public function syncCustomers(Request $request)
    {
        $request->validate([
            'IsSynced' => 'required|boolean',
        ]);

        try {
            // 1. Fetch data from SQL
            $data = DB::select("EXEC dbo.r_CustomerData @IsSynced = 0");

            if (empty($data)) {
                return response()->json([
                    'status' => 'empty',
                    'code' => 404,
                    'message' => 'Stored procedure executed but returned no data',
                    'data' => [],
                ], 404);
            }

            // 2. TRANSFORM THE DATA (Crucial Step)
            // We iterate through the results and convert the 'typesJson' string
            // into a real PHP array so Laravel outputs it as a nested JSON object.
            $formattedData = collect($data)->map(function ($item) {
                // Check if the field exists and isn't null
                if (! empty($item->TypesJson)) {
                    $item->TypesJson = json_decode($item->TypesJson);
                } else {
                    $item->TypesJson = []; // Ensure it's an array if null
                }

                return $item;
            });

            // 3. Return the transformed collection
            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'count' => $formattedData->count(),
                'message' => 'Customer Data Fetched Successfully',
                'data' => $formattedData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to sync customer data: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // Fetch Client Summary Statement
    public function getClientSummaryStatement(Request $request)
    {
        $request->validate([
            'ThirdPartyID' => 'required',
            'Type' => 'required|string',
        ]);

        try {
            $data = DB::select("EXEC p_GetClientStatement @ThirdPartyID = ?, @Type = ?", [$request->ThirdPartyID, $request->Type]);

            return response()->json([
                'status' => 'ok',
                'code' => 200,
                'message' => 'Client Summary Statement Fetched Successfully',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Failed to fetch client summary statement: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
