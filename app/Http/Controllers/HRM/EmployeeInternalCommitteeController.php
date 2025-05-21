<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HRM\Employee;
use App\Models\Auth\User;
use App\Models\HRM\Committee;
use App\Models\HRM\EmployeeInternalCommittee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeInternalCommitteeController extends Controller
{
    public function index()
    {
        return view('procurement.tendering.appointcommittee.index');
    }

    public function create()
    {
        return view('settings.committee.create')
            ->with('employees', Employee::all())
            ->with('committees', Committee::all());
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'Employee' => 'required|exists:t_Employees,Id',
            'Committee' => 'required|exists:t_Committees,Id',
        ]);

        $employeeId = $request->input('Employee');
        $committeeId = $request->input('Committee');
        $userId = Auth::id();
        $now = now();
  
        // Check if relationship exists and is not soft-deleted
        $exists = DB::table('t_Committee_Employee')
            ->where('EmployeeId', $employeeId)
            ->where('CommitteeId', $committeeId)
            ->whereNull('DeletedOn')
            ->exists();

        if ($exists) {
            return back()->withErrors(['Employee' => 'This employee is already assigned to the selected committee.']);
        }

        DB::table('t_Committee_Employee')->insert([
            'EmployeeId' => $employeeId,
            'CommitteeId' => $committeeId,
            'CreatedBy' => $userId,
            'CreatedOn' => $now,
            'ModifiedBy' => $userId,
            'ModifiedOn' => $now,
        ]);

        return redirect()->back()->with('success', 'Employee successfully added to committee.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'committee_id' => 'required|exists:t_Committees,Id',
            'employee_id' => 'required|exists:t_Employees,Id',
        ]);

        DB::table('t_Committee_Employee')
            ->where('CommitteeId', $request->committee_id)
            ->where('EmployeeId', $request->employee_id)
            ->delete();

        return back()->with('success', 'Employee removed from committee.');
    }

  

}
