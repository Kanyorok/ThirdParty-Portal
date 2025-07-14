<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GLMappingController extends Controller
{
    public function index()
    {
        $mappings = DB::table('t_GLPostingMap as m')
            ->leftJoin('t_TransactionTypes as t', 'm.TransactionTypeID', '=', 't.Id')
            ->select(
                'm.*',
                't.Code as TransactionCode',
                't.Description as TransactionDescription'
            )
            ->orderBy('m.Module')
            ->get();

        return view('finance.integration.glmapping.index', compact('mappings'));
    }

    public function create()
    {
        $transactionTypes = DB::table('t_TransactionTypes')->where('IsActive', 1)->get();
        return view('finance.integration.glmapping.create', compact('transactionTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Module' => 'required|string|max:50',
            'SourceDocType' => 'required|string|max:50',
            'TransactionType' => 'required|string|max:50',
            'DebitGL' => 'required|string|max:20',
            'CreditGL' => 'required|string|max:20',
            'PostingNarration' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        DB::table('t_GLPostingMap')->insert([
            'Module' => $validated['Module'],
            'SourceDocType' => $validated['SourceDocType'],
            'TransactionType' => $validated['TransactionType'],
            'DebitGL' => $validated['DebitGL'],
            'CreditGL' => $validated['CreditGL'],
            'PostingNarration' => $validated['PostingNarration'] ?? null,
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        return redirect()->route('glmapping.index')->with('success', 'GL Mapping saved successfully.');
    }
}
