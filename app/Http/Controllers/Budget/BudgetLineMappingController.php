<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetLine;
use Illuminate\Http\Request;

class BudgetLineMappingController extends Controller
{
    //
    public function index()
    {
        //Check if the user has permission to view using the enum set for budgetSetup
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetLine::class);
        return view('budgetandanalytics.budgetlinemapping.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetlinemapping.create');
    }
}
