<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use Illuminate\Http\Request;

class BudgetProductMasterController extends Controller
{
    //
    public function index()
    {
        // Fetch paginated products
        $products = BudgetProduct::paginate(25);

        // Transform into structured array
        $data = $products->map(function ($p) {
            return [
                'Code' => $p->ProductTypeID,
                'Name' => $p->Description,
                'Type' => BudgetProductType::where('ProductCode', $p->ProductTypeID)->pluck('Name')->first(),
                'GLCode' => BudgetGLMaster::where('AccountID', $p->GLAccountID)->pluck('GLAccountTypeID')->first(),
            ];
        });


        // Pass both data and paginator instance
        return view('budgetandanalytics.productmaster.index', compact('data', 'products'));
    }

    public function create()
    {
        return view('budgetandanalytics.productmaster.create');
    }
}
