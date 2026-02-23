<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Applicant;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::query()->orderBy('LastName');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhere('Phone', 'like', "%{$search}%");
            });
        }

        $applicants = $query->paginate(20);

        return view('hr.recruitment.applicants.index', compact('applicants'));
    }

    public function show($id)
    {
        $applicant = Applicant::with(['applications.opening'])->findOrFail($id);

        return view('hr.recruitment.applicants.show', compact('applicant'));
    }
}
