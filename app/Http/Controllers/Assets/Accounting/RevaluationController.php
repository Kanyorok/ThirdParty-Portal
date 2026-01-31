<?php

namespace App\Http\Controllers\Assets\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Assets\Accounting\AssetRevaluation;
use App\Models\Assets\Master\{AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\AssetBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevaluationController extends Controller
{
    public function index(Request $request)
    {
        $book = $request->get('book');
        $rows = AssetRevaluation::when($book, fn ($q) => $q->where('BookID', $book))
                ->orderByDesc('RevalDate')->paginate(20);
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.reval.index', compact('rows', 'books', 'book'));
    }

    public function create()
    {
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.reval.create', compact('books'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AssetID' => 'required|integer|exists:t_Assets,Id',
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'RevalDate' => 'required|date',
            'NewFairValue' => 'required|numeric|min:0',
            'Remarks' => 'nullable|max:250',
        ]);

        DB::transaction(function () use ($data) {
            $abv = AssetBookValue::where('AssetID', $data['AssetID'])->where('BookID', $data['BookID'])->lockForUpdate()->firstOrFail();
            $old = (float)$abv->NBV;

            AssetRevaluation::create([
                'AssetID' => $data['AssetID'],'BookID' => $data['BookID'],
                'RevalDate' => $data['RevalDate'],'OldNBV' => $old,'NewFairValue' => $data['NewFairValue'],
                'Remarks' => $data['Remarks'] ?? null,
            ]);

            // Simple gross-up: keep AccumDep, bump NBV to fair value (can be adjusted to full IAS16 model later)
            $abv->NBV = round((float)$data['NewFairValue'], 2);
            $abv->save();

            AssetHistory::create([
                'AssetID' => $data['AssetID'],'EventType' => 'Revaluation','EventDate' => $data['RevalDate'],
                'Reference' => 'REVAL','Remarks' => $data['Remarks'] ?? 'Revalued','CreatedOn' => now(),
            ]);
        });

        return redirect()->route('assets.acc.reval.index')->with('success', 'Revaluation recorded.');
    }
}
