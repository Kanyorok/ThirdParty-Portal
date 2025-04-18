<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SasraAuditorsImport;
use App\Exports\SasraAuditorsExport;
use App\Models\Procurement\SasraAuditor;

class SasraAuditorController extends Controller
{
    /**
     * Display a listing of the SASRA auditors.
     */
    public function index()
    {
        $auditors = SasraAuditor::orderBy('created_at', 'desc')->paginate(20);
        return view('procurement.sasra-auditors.index', compact('auditors'));
    }

    /**
     * Show the form for uploading the SASRA list.
     */
    public function showImportForm()
    {
        return view('procurement.sasra-auditors.upload');
    }

    /**
     * Store the uploaded SASRA list.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new SasraAuditorsImport, $request->file('file'));

        return redirect()->route('sasra-auditors.index')->with('success', 'SASRA auditor list uploaded successfully.');
    }

    /**
     * Download the SASRA auditor list as an Excel file.
     */
    public function download()
    {
        return Excel::download(new SasraAuditorsExport, 'sasra_auditors.xlsx');
    }
}
