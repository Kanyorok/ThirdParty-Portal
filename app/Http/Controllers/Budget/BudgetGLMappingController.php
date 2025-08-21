<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetGLMaster;
use Illuminate\Http\Request;

class BudgetGLMappingController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetGLAccount::class);
        $gls = BudgetGLMaster::all();
        return view('budgetandanalytics.glmapping.index', compact('gls'));
    }

    public function create()
    {
        return view('budgetandanalytics.glmapping.create');
    }
}
