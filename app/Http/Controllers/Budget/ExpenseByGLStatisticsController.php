<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpenseByGLStatisticsController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.expensesbyglcategory.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.expensesbyglcategory.create');
    }
}
