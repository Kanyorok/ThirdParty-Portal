<?php

namespace App\Http\Controllers\Assets\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Assets\Accounting\AssetImpairment;
use App\Models\Assets\Master\{AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\AssetBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImpairmentController extends Controller
{
    public function index(Request $request)
    {
        $book = $request->get('book');
        $rows = AssetImpairment::when($book, fn ($q) => $q->where('BookID', $book))
                ->orderByDesc('TestDate')->paginate(20);
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.impair.index', compact('rows', 'books', 'book'));
    }

    public function create()
    {
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.acc.impair.create', compact('books'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AssetID' => 'required|integer|exists:t_Assets,Id',
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'TestDate' => 'required|date',
            'RecoverableAmount' => 'required|numeric|min:0',
            'Remarks' => 'nullable|max:250',
        ]);

        DB::transaction(function () use ($data) {
            $abv = AssetBookValue::where('AssetID', $data['AssetID'])->where('BookID', $data['BookID'])->lockForUpdate()->firstOrFail();
            $old = (float)$abv->NBV;
            $rec = (float)$data['RecoverableAmount'];
            $loss = max(0.0, $old - $rec);

            AssetImpairment::create([
                'AssetID' => $data['AssetID'],'BookID' => $data['BookID'],
                'TestDate' => $data['TestDate'],'OldNBV' => $old,'RecoverableAmount' => $rec,
                'Remarks' => $data['Remarks'] ?? null,
            ]);

            if ($loss > 0) {
                $abv->NBV = round($rec, 2);
                $abv->save();
                AssetHistory::create([
                    'AssetID' => $data['AssetID'],'EventType' => 'Impairment','EventDate' => $data['TestDate'],
                    'Reference' => 'IMPAIR','Remarks' => "Loss: " . number_format($loss, 2),'CreatedOn' => now(),
                ]);
            }
        });

        return redirect()->route('assets.acc.impair.index')->with('success', 'Impairment recorded.');
    }
}
