<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;

//             'DispatchDate' => 'required|date',
//             'DispatchedTo' => 'required|string',
//             'DispatchMethod' => 'nullable|string',
//             'Status' => 'nullable|string',
//             'Remarks' => 'nullable|string',

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


class LegalDispatchController extends Controller
{
    public function index()
    {
        return view('legal.dispatch.index');
    }
}
