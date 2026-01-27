<?php

// AssetMeterController.php

namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\{AssetMeter};
use Illuminate\Http\Request;

class AssetMeterController extends Controller
{
    public function index(int $asset)
    {
        $rows = AssetMeter::where('AssetID', $asset)->orderBy('MeterName')->paginate(20);

        return view('assets.master.meters.index', compact('rows', 'asset'));
    }

    public function create(int $asset)
    {
        return view('assets.master.meters.create', compact('asset'));
    }

    public function store(Request $request, int $asset)
    {
        $data = $request->validate([
            'MeterName' => 'required|max:100',
            'Unit' => 'required|max:20',
            'ReadingType' => 'required|in:CUMULATIVE,GAUGE',
            'InitialReading' => 'required|numeric',
        ]);
        $data['AssetID'] = $asset;
        $data['CurrentReading'] = $data['InitialReading'];
        $data['LastReadingDate'] = now();
        AssetMeter::create($data);

        return back()->with('success', 'Meter added.');
    }

    public function edit(int $asset, int $id)
    {
        $row = AssetMeter::where('AssetID', $asset)->findOrFail($id);

        return view('assets.master.meters.edit', compact('asset', 'row'));
    }

    public function update(Request $request, int $asset, int $id)
    {
        $row = AssetMeter::where('AssetID', $asset)->findOrFail($id);
        $data = $request->validate([
            'MeterName' => 'required|max:100',
            'Unit' => 'required|max:20',
            'ReadingType' => 'required|in:CUMULATIVE,GAUGE',
            'InitialReading' => 'nullable|numeric',
        ]);
        $row->update($data);

        return back()->with('success', 'Meter updated.');
    }

    public function destroy(int $asset, int $id)
    {
        AssetMeter::where('AssetID', $asset)->where('Id', $id)->delete();

        return back()->with('success', 'Meter deleted.');
    }
}
