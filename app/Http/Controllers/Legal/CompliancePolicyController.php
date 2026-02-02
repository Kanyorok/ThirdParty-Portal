<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\CompliancePolicy;
use App\Models\Legal\CompliancePolicyAcknowledgment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompliancePolicyController extends Controller
{
    public function index()
    {
        $policies = CompliancePolicy::with('acknowledgments')->get();

        return view('legal.compliance.policies.index', compact('policies'));
    }

    public function create()
    {
        $categories = DB::table('t_PolicyCategories')->pluck('Name', 'Id');
        $areas = DB::table('t_ComplianceAreas')->pluck('Name', 'Id');

        return view('legal.compliance.policies.create', compact('categories', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'CategoryID' => 'nullable|exists:t_PolicyCategories,Id',
            'ComplianceAreaID' => 'nullable|exists:t_ComplianceAreas,Id',
            'EffectiveDate' => 'nullable|date',
            'Version' => 'nullable|string|max:50',
            'File' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        $fileName = null;
        $mime = null;
        $path = null;
        if ($request->hasFile('File')) {
            $file = $request->file('File');
            $fileName = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->store('compliance/policies');
        }

        CompliancePolicy::create([
            'Title' => $request->Title,
            'CategoryID' => $request->CategoryID,
            'ComplianceAreaID' => $request->ComplianceAreaID,
            'EffectiveDate' => $request->EffectiveDate,
            'Version' => $request->Version,
            'FileName' => $fileName,
            'MimeType' => $mime,
            'FilePath' => $path,
            'IsActive' => 1,
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.policies.index')->with('success', 'Policy created.');
    }

    public function show($id)
    {
        $policy = CompliancePolicy::with('acknowledgments')->findOrFail($id);
        $users = DB::table('t_Users')->pluck('Name', 'Id');

        return view('legal.compliance.policies.show', compact('policy', 'users'));
    }

    public function acknowledge($id)
    {
        CompliancePolicyAcknowledgment::create([
            'PolicyID' => $id,
            'UserID' => auth()->id() ?? 1,
            'AcknowledgedOn' => now(),
        ]);

        return back()->with('success', 'Policy acknowledged.');
    }
}
