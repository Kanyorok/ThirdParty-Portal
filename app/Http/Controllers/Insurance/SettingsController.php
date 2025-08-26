<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $codeId = $request->get('codeid');

        // Fetch unique CodeID types to populate filter dropdown
        $codeTypes = DB::table('t_CodeDetails')
            ->select('CodeID')
            ->distinct()
            ->pluck('CodeID')
            ->toArray();

        // Fetch code details based on filter
        $query = DB::table('t_CodeDetails')->orderBy('DisplayOrder');

        if ($codeId) {
            $query->where('CodeID', $codeId);
        }

        $codeDetails = DB::table('t_CodeDetails')
            ->select('ID', 'Description', 'DisplayOrder', 'IsActive')
            ->when($codeId, fn($query) => $query->where('CodeID', $codeId))
            ->orderBy('DisplayOrder')
            ->get();

        return view('bancassurance.settings.index', compact('codeDetails', 'codeTypes'));
    }


    public function store(Request $request)
    {
        DB::table('t_CodeDetails')->insert([
            'CodeID' => $request->CodeID,
            'Description' => $request->Description,
            'DisplayOrder' => $request->DisplayOrder,
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now()
        ]);

        return back()->with('success', 'Code detail added successfully.');
    }

    public function update(Request $request, $id)
    {
        DB::table('t_CodeDetails')->where('ID', $id)->update([
            'Description' => $request->Description,
            'DisplayOrder' => $request->DisplayOrder,
            'IsActive' => $request->has('IsActive') ? 1 : 0,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);

        return back()->with('success', 'Code detail updated.');
    }

    public function destroy($id)
    {
        DB::table('t_CodeDetails')->where('ID', $id)->delete();

        return back()->with('success', 'Code detail removed.');
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'CodeID' => 'required|string|max:100',
            'Description' => 'required|string|max:255',
            'DisplayOrder' => 'nullable|integer',
            'IsActive' => 'required|boolean',
        ]);

        $data = [
            'CodeID' => $validated['CodeID'],
            'Description' => $validated['Description'],
            'DisplayOrder' => $validated['DisplayOrder'] ?? null,
            'IsActive' => $validated['IsActive'],
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ];

        if ($request->has('id')) {
            // Update existing
            DB::table('t_CodeDetails')
                ->where('ID', $request->id)
                ->update($data);

            return redirect()->back()->with('success', 'Setting updated successfully.');
        } else {
            // Create new
            $data['CreatedBy'] = auth()->id();
            $data['CreatedOn'] = now();

            DB::table('t_CodeDetails')->insert($data);

            return redirect()->back()->with('success', 'Setting created successfully.');
        }
    }

    public function edit($id)
    {
        $record = DB::table('t_CodeDetails')->where('ID', $id)->first();

        if (!$record) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $codeTypes = DB::table('t_CodeDetails')->select('CodeID')->distinct()->pluck('CodeID')->toArray();

        return view('bancassurance.settings.edit', compact('record', 'codeTypes'));
    }

    public function create()
    {
        $codeTypes = DB::table('t_CodeDetails')
            ->select('CodeID')
            ->distinct()
            ->pluck('CodeID')
            ->toArray();

        return view('bancassurance.settings.create', compact('codeTypes'));
    }
}
