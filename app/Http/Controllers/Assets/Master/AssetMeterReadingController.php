<?php

// AssetMeterReadingController.php

namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller; // fixed namespace
use App\Models\Assets\Master\{AssetMeter, AssetMeterReading};
use Illuminate\Http\Request;

class AssetMeterReadingController extends Controller
{
    public function index(int $asset, int $meter)
    {
        $meterRow = AssetMeter::where('AssetID', $asset)->findOrFail($meter);
        $rows = AssetMeterReading::where('MeterID', $meter)->orderByDesc('ReadingDate')->paginate(20);

        return view('assets.master.meters.readings.index', compact('asset', 'meterRow', 'rows'));
    }

    public function create(int $asset, int $meter)
    {
        $meterRow = AssetMeter::where('AssetID', $asset)->findOrFail($meter);

        return view('assets.master.meters.readings.create', compact('asset', 'meterRow'));
    }

    public function store(Request $request, int $asset, int $meter)
    {
        $meterRow = AssetMeter::where('AssetID', $asset)->findOrFail($meter);
        $data = $request->validate([
            'Reading' => 'required|numeric',
            'ReadingDate' => 'required|date',
        ]);
        $data['MeterID'] = $meter;
        AssetMeterReading::create($data);

        // roll-up
        $meterRow->CurrentReading = $data['Reading'];
        $meterRow->LastReadingDate = $data['ReadingDate'];
        $meterRow->save();

        return back()->with('success', 'Reading captured.');
    }

    public function destroy(int $reading) // shallow route
    {
        $row = AssetMeterReading::findOrFail($reading);
        $row->delete();

        return back()->with('success', 'Reading deleted.');
    }
}
