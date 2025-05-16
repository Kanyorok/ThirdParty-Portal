<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\VendorClarifications;
use Illuminate\Http\Request;

class TenderclarificationController extends Controller
{
    public function index()
    {
        $clarifications = VendorClarifications::all();
        return view('procurement.tendering.suppliermanagement.clarificationhandling.index', compact('clarifications'));
    }

    public function create($clarification_id)
    {
        $clarification = VendorClarifications::findOrFail($clarification_id);
        return view('procurement.tendering.suppliermanagement.clarificationhandling.create', compact('clarification'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'clarification_id' => 'required|exists:t_vendorClarifications,ClarificationID',
            'question' => 'required|string',
            'answer' => 'required|string',
            'is_published_to_all' => 'boolean',
        ]);

        $clarification = VendorClarifications::findOrFail($request->clarification_id);

        $clarification->update([
            'Question' => $request->question,
            'Answer' => $request->answer,
            'AnswerDate' => now(),
            'ISPUBLISHEDTOALL' => $request->is_published_to_all ?? false,
            'CreatedBy' => $request->user()->Id,
            'ModifiedBy' => $request->user()->Id,
        ]);

        return redirect()->route('tenderclarification.index')->with('success', 'Clarification updated successfully.');
    }
}
