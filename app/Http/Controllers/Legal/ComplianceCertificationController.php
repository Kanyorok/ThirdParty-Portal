<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceCertification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceCertificationController extends Controller
{
    public function index()
    {
        $certs = ComplianceCertification::all();
        $users = DB::table('t_Users')->pluck('Name', 'Id');

        return view('legal.compliance.certifications.index', compact('certs', 'users'));
    }

    public function create()
    {
        $users = DB::table('t_Users')->pluck('Name', 'Id');

        return view('legal.compliance.certifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'UserID' => 'required|exists:t_Users,Id',
            'CertificationName' => 'required|string|max:200',
            'IssueDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date',
        ]);

        ComplianceCertification::create($validated + [
            'Status' => 'Active',
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.compliance.certifications.index')->with('success', 'Certification saved.');
    }
}
