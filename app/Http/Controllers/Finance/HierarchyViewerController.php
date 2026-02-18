<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceSyncGLAccount;

class HierarchyViewerController extends Controller
{
    public function index()
    {
        return view('finance.chartofaccounts.accounthierarchyviewer.index');
    }

    public function create()
    {
        return view('finance.chartofaccounts.accounthierarchyviewer.create');
    }

    public function hierarchy()
    {
        $accounts = FinanceSyncGLAccount::orderBy('GLCode')->get();

        return view('finance.chartofaccounts.chartofaccounts.account_hierarchy', compact('accounts'));
    }
}
