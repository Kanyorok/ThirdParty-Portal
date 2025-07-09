<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HierarchyViewerController extends Controller
{
    //
    public function index()
    {
        return view('finance.chartofaccounts.accounthierarchyviewer.index');
    }

    public function create(){
        return view('finance.chartofaccounts.accounthierarchyviewer.create');
    } 

    public function hierarchy()
{
    $accounts = DB::table('t_GLAccounts')
        ->orderBy('GLCode')
        ->get();

    return view('finance.chartofaccounts.chartofaccounts.account_hierarchy', compact('accounts'));
}
}
