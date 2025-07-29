<?php

namespace App\Http\Controllers\Insuarance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductLifecycleController extends Controller
{
    public function index()
    {
        $lifecycles = DB::table('t_InsuranceProviderProducts as ipp')
            ->join('t_InsuranceProviders as ip', 'ipp.InsuranceProviderID', '=', 'ip.Id')
            ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
            ->select(
                'ipp.Id',
                'ip.Name as ProviderName',
                'p.Name as ProductName',
                'ipp.IsActive',
                'ipp.CreatedAt'
            )
            ->orderByDesc('ipp.CreatedAt')
            ->get();

        return view('bancassurance.lifecycle.index', compact('lifecycles'));
    }

    public function toggleStatus($id)
    {
        $mapping = DB::table('t_InsuranceProviderProducts')->where('Id', $id)->first();

        if ($mapping) {
            $newStatus = $mapping->IsActive ? 0 : 1;

            DB::table('t_InsuranceProviderProducts')
                ->where('Id', $id)
                ->update(['IsActive' => $newStatus]);

            return redirect()->back()->with('success', 'Product status updated.');
        }

        return redirect()->back()->with('error', 'Product not found.');
    }
}
