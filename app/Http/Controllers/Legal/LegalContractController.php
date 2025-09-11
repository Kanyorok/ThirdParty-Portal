<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegalContractController extends Controller
{
    public function index()
    {
        return view('legal.contracts.index');
    }
}
<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalDocument;
use Illuminate\Support\Facades\Auth;

class LegalContractController extends Controller
{
    /**
     * Display a listing of contracts only.
     */
    public function index()
    {
        $contracts = LegalDocument::where('DocumentType', 'CONTRACT')
            ->orderByDesc('CreatedOn')
            ->get();

        return view('legal.maintenance.index', compact('contracts'));
    }

    /**
     * Show a single contract view (optional).
     */
    public function show($id)
    {
        $contract = LegalDocument::findOrFail($id);
        return view('legal.maintenance.show', compact('contract'));
    }

    public function review($id)
{
    $contract = LegalDocument::findOrFail($id);
    return view('legal.maintenance.review', compact('contract'));
}

public function submitReview(Request $request, $id)
{
    $request->validate([
        'ReviewStatus' => 'required|in:Draft,Reviewed,Approved,Rejected',
        'Remarks' => 'nullable|string|max:1000',
    ]);

    $contract = LegalDocument::findOrFail($id);
    $contract->ReviewStatus = $request->ReviewStatus;
    $contract->ReviewedBy = Auth::Id();
    $contract->ReviewedOn = now();
    $contract->Remarks = $request->Remarks;
    $contract->save();

    return redirect()->route('legal.maintenance.index')->with('success', 'Review submitted.');
}

}
