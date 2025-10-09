<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductLifecycleController extends Controller
{
    public function index()
    {
        $products = DB::table('t_InsuranceProducts')
            ->select('Id', 'ProductName', 'ProductCode', 'IsActive', 'CreatedOn')
            ->orderBy('ProductName')
            ->get();

        return view('insurance.productlifecycle.index', compact('products'));
    }

    public function toggleStatus($id)
    {
        try {
            $product = DB::table('t_InsuranceProducts')->where('Id', $id)->first();

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $newStatus = !$product->IsActive;

            DB::table('t_InsuranceProducts')
                ->where('Id', $id)
                ->update([
                    'IsActive' => $newStatus,
                    'ModifiedBy' => Auth::id() ?? 1,
                    'ModifiedOn' => now(),
                ]);

            return response()->json([
                'message' => 'Product status updated successfully',
                'isActive' => $newStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle product status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update product status'], 500);
        }
    }
}
