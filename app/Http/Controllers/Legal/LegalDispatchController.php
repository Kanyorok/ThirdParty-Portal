<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use App\Models\Legal\LegalDocument;
// use App\Models\Legal\LegalDispatch;
// use Carbon\Carbon;

// class LegalDispatchController extends Controller
// {
//     public function index($documentId)
//     {
//         $document = LegalDocument::findOrFail($documentId);
//         $dispatches = LegalDispatch::where('LegalDocumentID', $documentId)
//             ->where('IsActive', 1)
//             ->orderByDesc('DispatchDate')
//             ->get();

//         return view('legal.dispatches.index', compact('document', 'dispatches'));
//     }

//     public function create($documentId)
//     {
//         $document = LegalDocument::findOrFail($documentId);
//         return view('legal.dispatches.create', compact('document'));
//     }

//     public function store(Request $request, $documentId)
//     {
//         $request->validate([
//             'DispatchDate' => 'required|date',
//             'DispatchedTo' => 'required|string',
//             'DispatchMethod' => 'nullable|string',
//             'Status' => 'nullable|string',
//             'Remarks' => 'nullable|string',
//         ]);

//             LegalDispatch::create([
//                 'LegalDocumentID' => $documentId,
//                 'DispatchDate' => Carbon::parse($request->DispatchDate),
//                 'DispatchedTo' => $request->DispatchedTo,
//                 'DispatchMethod' => $request->DispatchMethod,
//                 'Status' => $request->Status ?? 'Pending',
//                 'Remarks' => $request->Remarks,
//                 'CreatedBy' => Auth::id(),
//                 'CreatedOn' => now(),
//                 'IsActive' => 1
//             ]);


class LegalDispatchController extends Controller
{
    public function index()
    {
        return view('legal.dispatch.index');
    }
}
