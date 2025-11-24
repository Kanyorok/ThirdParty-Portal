<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use App\Models\Legal\LegalIntellectualProperty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalIntellectualPropertyController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::IntellectualPropertyView, LegalIntellectualProperty::class);

        $records = LegalIntellectualProperty::orderByDesc('CreatedOn')->paginate(15);
        return view('legal.intellectual.index', compact('records'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::IntellectualPropertyCreate, LegalIntellectualProperty::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID', 'IPTypes')
            ->get();
        return view('legal.intellectual.create', compact('details'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::IntellectualPropertyCreate, LegalIntellectualProperty::class);

        $validated = $request->validate([
            'IPType' => 'required|exists:t_CodeDetails,Value',
            'Title' => 'required|string',
            'Owner' => 'required|string',
            'RegistrationNumber' => 'required|string',
            'RegistrationDate' => 'required|date',
            'ExpiryDate' => 'required|date',
            'Remarks' => 'required|string',
            'IsDisputed' => 'boolean',
            'DisputeReason' => 'nullable|string',
        ]);

        $duplicates = LegalIntellectualProperty::where('Title', $validated['Title'])
            ->where('Owner', $validated['Owner'])
            ->where('RegistrationNumber', $validated['RegistrationNumber'])
            ->exists();

        if ($duplicates) {
            return back()->with('error', 'Error there is alreaady an existing record with these details');
        }
        try {
            DB::beginTransaction();

            LegalIntellectualProperty::create([
                'IPType' => $validated['IPType'],
                'Title' => $validated['Title'],
                'Owner' => $validated['Owner'],
                'RegistrationNumber' => $validated['RegistrationNumber'],
                'RegistrationDate' => $validated['RegistrationDate'],
                'ExpiryDate' => $validated['ExpiryDate'],
                'Status' => $validated['Status'] ?? 'Inactive',
                'Remarks' => $validated['Remarks'],
                'IsDisputed' => $validated['IsDisputed'] ?? false,
                'DisputeReason' => $validated['DisputeReason'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Intellectual Property successfully created');

            DB::commit();

            return redirect()->route('legal.intellectual.index')->with('success', 'Intellectual Property registered successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Error creating Intellectual Property');

            Log::error('Error creating Intellectual Property.' . $th->getMessage());
            return back()->with('error', 'Error creating Intellectual Property: ' . $th->getMessage());

        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::IntellectualPropertyView, LegalIntellectualProperty::class);

        $record = LegalIntellectualProperty::findOrFail($id);
        return view('legal.intellectual.show', compact('record'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::IntellectualPropertyUpdate, LegalIntellectualProperty::class);

        $record = LegalIntellectualProperty::findOrFail($id);
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'IPTypes')
            ->get();
        return view('legal.intellectual.edit', compact('record', 'details'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::IntellectualPropertyUpdate, LegalIntellectualProperty::class);

        $record = LegalIntellectualProperty::findOrFail($id);

        $validated = $request->validate([
            'IPType' => 'required|string',
            'Title' => 'required|string',
            'Owner' => 'required|string',
            'RegistrationNumber' => 'required|string',
            'RegistrationDate' => 'required|date',
            'ExpiryDate' => 'required|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
            'IsDisputed' => 'boolean',
            'DisputeReason' => 'nullable|string',
        ]);
        try {

            $validated['ModifiedBy'] = Auth::id();
            $validated['ModifiedOn'] = now();

            $record->update($validated);

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Intellectual Property successfully updated');

            DB::commit();

            return redirect()->route('legal.intellectual.index')->with('success', 'Intellectual Property record updated.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Error updating Intellectual Property');

            Log::error('Error updating Intellectual Property.' . $th->getMessage());
            return back()->with('error', 'Error updating Intellectual Property: ' . $th->getMessage());
        }
    }

    public function raiseDispute(Request $request, $id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        $validated = $request->validate([
            'IsDisputed' => 'required|boolean',
            'DisputeReason' => 'required|string',
        ]);

        $record->update([
            'IsDisputed' => $validated['IsDisputed'],
            'DisputeReason' => $validated['DisputeReason'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.intellectual.index')->with('success', 'Dispute raised successfully.');
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::IntellectualPropertyDelete, LegalIntellectualProperty::class);

        try {
            DB::beginTransaction();

            $record = LegalIntellectualProperty::findOrFail($id);
            $record->DeletedBy = Auth::id();
            $record->save();
            $record->delete();

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Intellectual Property successfully deleted');

            DB::commit();

            return back()->with('success', 'Record de;eted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalIntellectualProperty())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Error deleting Intellectual Property');

            Log::error('Error deleting Intellectual Property.' . $th->getMessage());
            return back()->with('error', 'Error deleting Intellectual Property: ' . $th->getMessage());
        }
    }
}
