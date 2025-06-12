<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use Illuminate\Http\Request;

class BudgetProductMasterController extends Controller
{
    //
    public function index()
    {
        $products = BudgetProduct::all();
        $data = [];
        foreach ($products as $p) {
            $data[] = [
                'Code' => $p->ProductTypeID,
                'Name' => $p->Description,
                'Type' => BudgetProductType::find($p->CBSProductID)->Name,
                'GLCode' => BudgetGLAccount::find($p->GLAccountID)->GTType,
            ];
        }
        return view('budgetandanalytics.productmaster.index', compact('data'));
    }

    public function create()
    {
        return view('budgetandanalytics.productmaster.create');
    }
}
