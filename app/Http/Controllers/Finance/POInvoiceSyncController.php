<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POInvoiceSyncController extends Controller
{
    public function index()
    {
        $syncRules = DB::table('t_POInvoiceSyncSettings')->orderBy('POType')->get();
        return view('finance.integration.po_invoice_sync.index', compact('syncRules'));
    }

    public function create()
    {
        return view('finance.integration.po_invoice_sync.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_type' => 'required|string|max:50',
            'gl_account' => 'required|string|max:20',
            'auto_sync' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        DB::table('t_POInvoiceSyncSettings')->insert([
            'POType' => $validated['po_type'],
            'DefaultGLAccount' => $validated['gl_account'],
            'AutoSyncEnabled' => $validated['auto_sync'],
            'IsActive' => $validated['status'],
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        return redirect()->route('po-invoice-sync.index')->with('success', 'Sync rule saved successfully.');
    }
}
