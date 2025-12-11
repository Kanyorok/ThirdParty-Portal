<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->whereYear('HolidayDate', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $holidays = $query->orderBy('HolidayDate')->paginate(50);

        return view('hr.config.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('hr.config.holidays.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name'        => 'required|string|max:150',
            'HolidayDate' => 'required|date',
            'Region'      => 'nullable|string|max:100',
            'IsRecurring' => 'nullable|boolean',
        ]);

        $data['IsRecurring'] = $request->boolean('IsRecurring');
        $data['IsActive']    = 1;
        $data['Status']      = 'Pending';
        $data['CreatedBy']   = auth()->id();
        $data['CreatedOn']   = now();

        Holiday::create($data);

        return redirect()
            ->route('hr.config.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit($id)
    {
        $holiday = Holiday::findOrFail($id);

        return view('hr.config.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $data = $request->validate([
            'Name'        => 'required|string|max:150',
            'HolidayDate' => 'required|date',
            'Region'      => 'nullable|string|max:100',
            'IsRecurring' => 'nullable|boolean',
            'IsActive'    => 'nullable|boolean',
        ]);

        $data['IsRecurring'] = $request->boolean('IsRecurring');
        $data['IsActive']    = $request->has('IsActive') ? $request->boolean('IsActive') : $holiday->IsActive;
        $data['ModifiedBy']  = auth()->id();
        $data['ModifiedOn']  = now();

        $holiday->update($data);

        return redirect()
            ->route('hr.config.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);

        $holiday->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()
            ->route('hr.config.holidays.index')
            ->with('success', 'Holiday deactivated successfully.');
    }

    public function approve($id)
    {
        $holiday = Holiday::findOrFail($id);

        $holiday->update([
            'Status'     => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
        ]);

        return back()->with('success', 'Holiday approved successfully.');
    }
}
