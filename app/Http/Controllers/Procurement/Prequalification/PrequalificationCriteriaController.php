<?php

namespace App\Http\Controllers\Procurement\Prequalification;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierCategory;

class PrequalificationCriteriaController extends Controller
{
    public function index()
    {
        $supplierCategories = SupplierCategory::query()
            ->select('SupplierCategoryID', 'CategoryName', 'Description', 'IsActive')
            ->orderBy('CategoryName')
            ->get();

        return view(
            'procurement.suppliers.prequalification.prequalification-criteria-setup.index',
            compact('supplierCategories')
        );
    }

    public function create()
    {
        return view('procurement.suppliers.prequalification.criteria.create');
    }
}
