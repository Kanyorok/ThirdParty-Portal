<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetGLMaster;

class BudgetGLMappingController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetGLAccount::class);

        // Fetch with pagination
        $gls = BudgetGLMaster::select(
            'BudgetGLID',
            'AccountID',
            'Description',
            'CurrencyID',
            'GLAccountTypeID',
            'GLSubAccountTypeID'
        )->paginate(25);

        return view('budgetandanalytics.glmapping.index', compact('gls'));
    }

    public function create()
    {
        return view('budgetandanalytics.glmapping.create');
    }
}
