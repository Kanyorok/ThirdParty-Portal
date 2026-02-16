<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveType::query();

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $types = $query->orderBy('Name')->paginate(50);

        return view('hr.config.leavetypes.index', compact('types'));
    }

    public function create()
    {
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.leavetypes.create', compact('grades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|string|max:50|unique:t_HRLeaveTypes,Code',
            'Name' => 'required|string|max:150',
            'AnnualEntitlementDays' => 'required|integer|min:0',
            'AllowedGender' => 'nullable|string|in:Male,Female',
            'AllowCarryForward' => 'nullable|boolean',
            'MaxCarryForwardDays' => 'nullable|integer|min:0',
            'RequiresAttachment' => 'nullable|boolean',
            'IsPaid' => 'nullable|boolean',
            'GradeIDs' => 'nullable|array',
            'GradeIDs.*' => 'integer',
        ]);

        $data['AllowCarryForward'] = $request->boolean('AllowCarryForward');
        $data['RequiresAttachment'] = $request->boolean('RequiresAttachment');
        $data['IsPaid'] = $request->boolean('IsPaid', true);
        $data['IsActive'] = 1;
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $gradeIds = $data['GradeIDs'] ?? [];
        unset($data['GradeIDs']);

        $type = LeaveType::create($data);
        if ($gradeIds) {
            $type->grades()->sync($gradeIds);
        }

        return redirect()
            ->route('hr.config.leavetypes.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function edit($id)
    {
        $type = LeaveType::findOrFail($id);

        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.config.leavetypes.edit', compact('type', 'grades'));
    }

    public function update(Request $request, $id)
    {
        $type = LeaveType::findOrFail($id);

        $data = $request->validate([
            'Name' => 'required|string|max:150',
            'AnnualEntitlementDays' => 'required|integer|min:0',
            'AllowedGender' => 'nullable|string|in:Male,Female',
            'AllowCarryForward' => 'nullable|boolean',
            'MaxCarryForwardDays' => 'nullable|integer|min:0',
            'RequiresAttachment' => 'nullable|boolean',
            'IsPaid' => 'nullable|boolean',
            'IsActive' => 'nullable|boolean',
            'GradeIDs' => 'nullable|array',
            'GradeIDs.*' => 'integer',
        ]);

        $data['AllowCarryForward'] = $request->boolean('AllowCarryForward');
        $data['RequiresAttachment'] = $request->boolean('RequiresAttachment');
        $data['IsPaid'] = $request->boolean('IsPaid', true);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $type->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $gradeIds = $data['GradeIDs'] ?? [];
        unset($data['GradeIDs']);

        $type->update($data);
        $type->grades()->sync($gradeIds);

        return redirect()
            ->route('hr.config.leavetypes.index')
            ->with('success', 'Leave type updated successfully.');
    }

    public function destroy($id)
    {
        $type = LeaveType::findOrFail($id);

        $type->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()
            ->route('hr.config.leavetypes.index')
            ->with('success', 'Leave type deactivated successfully.');
    }

    public function approve($id)
    {
        $type = LeaveType::findOrFail($id);

        $type->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
        ]);

        return back()->with('success', 'Leave type approved successfully.');
    }
}
