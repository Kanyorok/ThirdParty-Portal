<?php

namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\{AssetCalibrationEvent, AssetMeter};
use App\Models\Assets\Settings\AssetServiceProvider;
use Illuminate\Http\Request;

class AssetCalibrationController extends Controller
{
    public function index(int $asset)
    {
        $rows = AssetCalibrationEvent::where('AssetID', $asset)->orderByDesc('CalibrationDate')->paginate(20);
        $providers = AssetServiceProvider::orderBy('Name')->get();
        $meters = AssetMeter::where('AssetID', $asset)->orderBy('MeterName')->get();

        return view('assets.master.calibrations.index', compact('rows', 'asset', 'providers', 'meters'));
    }

    public function create(int $asset)
    {
        $providers = AssetServiceProvider::orderBy('Name')->get();
        $meters = AssetMeter::where('AssetID', $asset)->orderBy('MeterName')->get();

        return view('assets.master.calibrations.create', compact('asset', 'providers', 'meters'));
    }

    public function store(Request $request, int $asset)
    {
        $data = $request->validate([
            'MeterID' => 'nullable|integer',
            'ProviderID' => 'nullable|integer|exists:t_AssetServiceProviders,Id',
            'CertificateNo' => 'nullable|max:80',
            'CalibrationDate' => 'required|date',
            'NextDueDate' => 'nullable|date',
            'Result' => 'required|in:PASS,FAIL',
            'Remarks' => 'nullable|string',
        ]);
        $data['AssetID'] = $asset;
        AssetCalibrationEvent::create($data);

        return back()->with('success', 'Calibration recorded.');
    }

    public function destroy(int $asset, int $id)
    {
        AssetCalibrationEvent::where('AssetID', $asset)->where('Id', $id)->delete();

        return back()->with('success', 'Calibration deleted.');
    }
}
