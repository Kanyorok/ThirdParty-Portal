<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Legal\LegalClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalClauseController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::ContractView, LegalClause::class);

        $clauses = LegalClause::all();
        return view('legal.clauses.index', compact('clauses'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::ContractCreate, LegalClause::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID', 'ClauseTypes')
            ->get();

        return view('legal.clauses.create', compact('details'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::ContractCreate, LegalClause::class);

        $validated = $request->validate([
                'Title' => 'required|string|max:255',
                'ClauseType' => 'required|string|max:100',
                'Content' => 'required|string',
                'Version' => 'required|string|max:50',
        ]);

        $duplicates = LegalClause::where('Title', $validated['Title'])
            ->where('ClauseType', $validated['ClauseType'])
            ->where('Version', $validated['Version'])
            ->exists();

            if ($duplicates) {
                return back()->with('error', 'This clause (same title, type, and version) already exists.');
            }

        try {
            DB::beginTransaction();

            $clause = LegalClause::create([
                'Title' => $validated['Title'],
                'ClauseType' => $validated['ClauseType'],
                'Content' => $validated['Content'],
                'Version' => $validated['Version'],
                'IsStandard' => $request->has('IsStandard') ? 'Yes' : 'No',
                'ClauseDMSDocID' => $request->ClauseDMSDocID ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn($clause)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Clause created successfully');

            DB::commit();

            return redirect()->route('legal.clauses.index')->with('success', 'Clause created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalClause())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Error creating clause');

            Log::error('Error creating clause: ' . $th->getMessage());
            return back()->with('error', 'Error creating clause: ' . $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::ContractUpdate, LegalClause::class);

        $clause = LegalClause::findOrFail($id);
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'ClauseTypes')
            ->get();

        return view('legal.clauses.edit', compact('clause', 'details'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::ContractUpdate, LegalClause::class);

        $clause = LegalClause::findOrFail($id);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'ClauseType' => 'nullable|string|max:100',
            'Content' => 'required|string',
            'Version' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $clause->update([
                'Title' => $validated['Title'],
                'ClauseType' => $validated['ClauseType'],
                'Content' => $validated['Content'],
                'IsStandard' => $request->has('IsStandard') ? 'Yes' : 'No',
                'Version' => $validated['Version'],
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            activity()
                ->performedOn($clause)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Clause updated successfully');

            DB::commit();

            return redirect()->route('legal.clauses.index')->with('success', 'Clause updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn($clause)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Error updating clause');

            Log::error('Error updating clause: ' . $th->getMessage());
            return back()->with('error', 'Error updating clause: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::ContractDelete, LegalClause::class);

        try {
            DB::beginTransaction();

            $clause = LegalClause::findOrFail($id);
            $clause->DeletedBy = Auth::id();
            $clause->save();
            $clause->delete();

            activity()
                ->performedOn($clause)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Clause deleted');

            DB::commit();

            return redirect()->route('legal.clauses.index')->with('success', 'Clause deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalClause())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Error deleting clause');

            Log::error('Error deleting clause: ' . $th->getMessage());
            return back()->with('error', 'Error deleting clause: ' . $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::ContractView, LegalClause::class);

        $clause = LegalClause::findOrFail($id);
        return view('legal.clauses.show', compact('clause'));
    }
}
