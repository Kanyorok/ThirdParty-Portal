<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Country;
use App\Models\HR\Holiday;
use App\Models\HR\Religion;
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
        $countries = Country::select(['Id', 'Name'])->active()->ordered()->get()->keyBy('Id');

        return view('hr.config.holidays.index', compact('holidays', 'countries'));
    }

    public function create()
    {
        $countries = Country::select(['Id', 'Name'])->active()->ordered()->get();
        $religions = Religion::where('IsActive', 1)->orderBy('Name')->get(['Name']);

        return view('hr.config.holidays.create', compact('countries', 'religions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => 'required|string|max:150',
            'HolidayDate' => 'required|date',
            'AppliesToReligion' => 'nullable|string|max:100',
            'IsRecurring' => 'nullable|boolean',
            'CountryId' => 'nullable|integer|exists:t_Countries,Id',
        ]);

        $regionScope = $request->boolean('IsRegional') ? 'Regional' : 'Global';
        if ($regionScope === 'Regional' && ! $request->filled('CountryId')) {
            return back()->withErrors(['CountryId' => 'Select a country for regional holidays.'])->withInput();
        }

        $data['IsRecurring'] = $request->boolean('IsRecurring');
        $data['RegionScope'] = $regionScope;
        $data['CountryId'] = $regionScope === 'Regional' ? $request->input('CountryId') : null;
        $data['IsActive'] = 1;
        $data['Status'] = 'Pending';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        Holiday::create($data);

        return redirect()
            ->route('hr.config.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit($id)
    {
        $holiday = Holiday::findOrFail($id);

        $countries = Country::select(['Id', 'Name'])->active()->ordered()->get();
        $religions = Religion::where('IsActive', 1)->orderBy('Name')->get(['Name']);

        return view('hr.config.holidays.edit', compact('holiday', 'countries', 'religions'));
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $data = $request->validate([
            'Name' => 'required|string|max:150',
            'HolidayDate' => 'required|date',
            'AppliesToReligion' => 'nullable|string|max:100',
            'IsRecurring' => 'nullable|boolean',
            'IsActive' => 'nullable|boolean',
            'CountryId' => 'nullable|integer|exists:t_Countries,Id',
        ]);

        $regionScope = $request->boolean('IsRegional') ? 'Regional' : 'Global';
        if ($regionScope === 'Regional' && ! $request->filled('CountryId')) {
            return back()->withErrors(['CountryId' => 'Select a country for regional holidays.'])->withInput();
        }

        $data['IsRecurring'] = $request->boolean('IsRecurring');
        $data['RegionScope'] = $regionScope;
        $data['CountryId'] = $regionScope === 'Regional' ? $request->input('CountryId') : null;
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $holiday->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $holiday->update($data);

        return redirect()
            ->route('hr.config.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);

        $holiday->update([
            'IsActive' => 0,
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
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
        ]);

        return back()->with('success', 'Holiday approved successfully.');
    }
}
