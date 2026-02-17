<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeePromotion;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeePromotionController extends Controller
{
    public function index()
    {
        $promotions = EmployeePromotion::with(['employee'])->orderByDesc('Id')->paginate(20);

        return view('hr.movements.promotions.index', compact('promotions'));
    }

    public function create()
    {
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.movements.promotions.create', compact('employees', 'grades', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        EmployeePromotion::create($data);

        return redirect()->route('hr.movements.promotions.index')->with('success', 'Promotion request submitted.');
    }

    public function show($id)
    {
        $promotion = EmployeePromotion::with('employee')->findOrFail($id);

        return view('hr.movements.promotions.show', compact('promotion'));
    }

    public function edit($id)
    {
        $promotion = EmployeePromotion::findOrFail($id);
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.movements.promotions.edit', compact('promotion', 'employees', 'grades', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $promotion = EmployeePromotion::findOrFail($id);
        $data = $this->validateData($request, false);
        $promotion->update($data + [
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.movements.promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy($id)
    {
        $promotion = EmployeePromotion::findOrFail($id);
        $promotion->update([
            'Status' => 'Rejected',
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.movements.promotions.index')->with('success', 'Promotion marked rejected.');
    }

    public function approve($id, Request $request)
    {
        $promotion = EmployeePromotion::findOrFail($id);
        $promotion->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);

        return redirect()->route('hr.movements.promotions.index')->with('success', 'Promotion approved.');
    }

    public function reject($id, Request $request)
    {
        $promotion = EmployeePromotion::findOrFail($id);
        $promotion->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);

        return redirect()->route('hr.movements.promotions.index')->with('success', 'Promotion rejected.');
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        return $request->validate([
            'EmployeeID' => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'FromGradeID' => 'nullable|integer',
            'ToGradeID' => 'nullable|integer',
            'FromRoleID' => 'nullable|integer',
            'ToRoleID' => 'nullable|integer',
            'FromSalary' => 'nullable|numeric|min:0',
            'ToSalary' => 'nullable|numeric|min:0',
            'EffectiveDate' => 'required|date',
            'Reason' => 'nullable|string|max:255',
            'Status' => ['nullable', Rule::in(['Pending','Approved','Rejected'])],
        ]);
    }
}
