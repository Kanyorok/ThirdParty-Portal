<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceException;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class AttendanceExceptionController extends Controller
{
    public function index()
    {
        $exceptions = AttendanceException::orderByDesc('Id')->paginate(20);
        return view('hr.attendance.exceptions.index', compact('exceptions'));
    }

    public function resolve($id, Request $request)
    {
        $exception = AttendanceException::findOrFail($id);
        $exception->update([
            'Status' => 'Resolved',
            'Resolution' => $request->input('Resolution'),
            'ResolvedBy' => auth()->id(),
            'ResolvedOn' => now(),
        ]);
        return redirect()->route('hr.attendance.exceptions.index')->with('success', 'Exception resolved.');
    }
}
