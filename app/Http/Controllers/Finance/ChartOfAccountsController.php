<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        return view('finance.chartofaccounts.chartofaccounts.index');
    }

    public function create()
    {
        $accountTypes = DB::table('t_GLAccountTypes')->get();
        $typeGroups = DB::table('t_GLTypeGroups')->get();
        $subAccountTypes = DB::table('t_GLSubAccountTypes')->get();
        $allGLAccounts = DB::table('t_GLAccounts')->select('GLCode', 'AccountName')->get();

        return view('finance.chartofaccounts.chartofaccounts.create', compact(
            'accountTypes', 'typeGroups', 'subAccountTypes', 'allGLAccounts'
        ));
    }

    public function getTypeGroups($accountTypeId)
    {
        $groups = DB::table('t_GLTypeGroups')
            ->where('GLAccountTypeID', $accountTypeId)
            ->select('Id', 'Description')
            ->get();

        return response()->json($groups);
    }

    public function getSubTypes($typeGroupId)
    {
        $subTypes = DB::table('t_GLSubAccountTypes')
            ->where('GLTypeGroupID', $typeGroupId)
            ->select('Id', 'Description')
            ->get();

        return response()->json($subTypes);
    }

    public function hierarchy()
    {
        $accounts = DB::table('t_GLAccounts')->orderBy('GLCode')->get();
        return view('finance.chartofaccounts.chartofaccounts.account_hierarchy', compact('accounts'));
    }

    public function show($code)
    {
        $account = DB::table('t_GLAccounts')->where('GLCode', $code)->first();

        if (!$account) {
            abort(404, 'Account not found.');
        }

        return view('finance.chartofaccounts.chartofaccounts.show', compact('account'));
    }
}
