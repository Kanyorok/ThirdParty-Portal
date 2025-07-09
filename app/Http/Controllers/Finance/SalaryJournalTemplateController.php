<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryJournalTemplateController extends Controller
{
    public function index()
    {
        $templates = DB::table('t_SalaryJournalTemplates')->orderBy('TemplateName')->get();
        return view('finance.integration.salary_journal.index', compact('templates'));
    }

    public function create()
    {
        return view('finance.integration.salary_journal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TemplateName' => 'required|string|max:100',
            'ComponentCode' => 'required|string|max:50',
            'DebitGLAccount' => 'required|string|max:50',
            'CreditGLAccount' => 'required|string|max:50',
            'BranchCode' => 'nullable|string|max:20',
            'DepartmentCode' => 'nullable|string|max:20',
            'IsActive' => 'required|boolean',
        ]);

        DB::table('t_SalaryJournalTemplates')->insert([
            'TemplateName' => $request->TemplateName,
            'ComponentCode' => $request->ComponentCode,
            'DebitGLAccount' => $request->DebitGLAccount,
            'CreditGLAccount' => $request->CreditGLAccount,
            'BranchCode' => $request->BranchCode,
            'DepartmentCode' => $request->DepartmentCode,
            'IsActive' => $request->IsActive,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        return redirect()->route('salary-journal-templates.index')->with('success', 'Template created successfully.');
    }
}
