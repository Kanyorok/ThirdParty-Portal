<?php

namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\AssetHistory;
use Illuminate\Http\Request;

class AssetHistoryController extends Controller
{
    public function index(int $asset)
    {
        $rows = AssetHistory::where('AssetID', $asset)->orderByDesc('EventDate')->paginate(30);

        return view('assets.master.history.index', compact('rows', 'asset'));
    }

    public function store(Request $request, int $asset)
    {
        $data = $request->validate([
            'EventType' => 'required|max:50',
            'EventDate' => 'required|date',
            'Reference' => 'nullable|max:100',
            'Remarks' => 'nullable|string',
        ]);
        $data['AssetID'] = $asset;
        AssetHistory::create($data);

        return back()->with('success', 'History event logged.');
    }

    public function destroy(int $asset, int $id)
    {
        AssetHistory::where('AssetID', $asset)->where('Id', $id)->delete();

        return back()->with('success', 'History event deleted.');
    }
}
