<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\Finance\FinanceTransaction;
use App\Models\HRM\Department;
use Illuminate\Http\Request;

class LedgerReportController extends Controller
{
    public function index(Request $request)
    {
        // Dropdown data
        $glAccount = FinanceGLAccounts::select('Id', 'GLName', 'GLCode')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $departments = Department::select('Id', 'Name')->get();

        // // If dates must be required, keep this
        //     'FromDate' => 'required|date',
        //     'ToDate'   => 'required|date|after_or_equal:FromDate',
        // ]);

        $query = FinanceTransaction::with(
            'glAccounts:Id,GLName,GLCode',
            'branches:Id,Name',
            'departments:Id,Name'
        )
            ->select(
                'TransactionDate',
                'ReferenceNumber',
                'SystemDescription',
                'BranchID',
                'DepartmentID',
                'GLAccountID',
                'DRCR',
                'Amount'
            )
            ->whereBetween('TransactionDate', [$request->FromDate, $request->ToDate]);

        if ($request->filled('GLAccount') && $request->GLAccount !== 'All') {
            $query->where('GLAccountID', $request->GLAccount);
        }

        if ($request->filled('Branch') && $request->Branch !== 'All') {
            $query->where('BranchID', $request->Branch);
        }

        if ($request->filled('Department') && $request->Department !== 'All') {
            $query->where('DepartmentID', $request->Department);
        }

        $reports = $query->orderBy('TransactionDate', 'asc')->get();

        return view(
            'finance.generalledger.glreporting.ledgerreport.index',
            compact('reports', 'glAccount', 'branches', 'departments')
        );
    }

    public function create()
    {
        return view('finance.generalledger.glreporting.ledgerreport.create');
    }
}
