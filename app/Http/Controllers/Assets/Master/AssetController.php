<?php

namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\{Asset, AssetBookValue, AssetHistory};
use App\Models\Assets\Settings\{AssetBook, AssetServiceProvider, FixedAssetClass};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $classId = $request->get('class');
        $status = $request->get('status');

        $rows = Asset::when($q, fn ($qq) =>
                        $qq->where('AssetCode', 'like', "%$q%")
                           ->orWhere('AssetName', 'like', "%$q%")
                           ->orWhere('SerialNumber', 'like', "%$q%"))
                     ->when($classId, fn ($qq) => $qq->where('ClassID', $classId))
                     ->when($status, fn ($qq) => $qq->where('Status', $status))
                     ->orderBy('AssetCode')
                     ->paginate(20);

        $classes = FixedAssetClass::orderBy('Name')->get(['Id','Code','Name']);

        return view('assets.master.register.index', compact('rows', 'q', 'classId', 'status', 'classes'));
    }

    public function create()
    {
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = \App\Models\Assets\Settings\AssetLocation::orderBy('Site')->get();
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.master.register.create', compact('classes', 'locations', 'books'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AssetCode' => 'required|max:50|unique:t_Assets,AssetCode',
            'AssetName' => 'required|max:200',
            'ClassID' => 'required|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'AcquisitionDate' => 'nullable|date',
            'CapitalizationDate' => 'nullable|date',
            'Status' => 'required|in:Active,Inactive,Disposed',
            'Manufacturer' => 'nullable|max:120',
            'Model' => 'nullable|max:120',
            'SerialNumber' => 'nullable|max:120',
            'TagNo' => 'nullable|max:120',
            'Notes' => 'nullable|string',
            // optional initial financials per book
            'Book.*.BookID' => 'nullable|integer|exists:t_AssetBooks,Id',
            'Book.*.AcquisitionCost' => 'nullable|numeric|min:0',
            'Book.*.DepMethod' => 'nullable|in:SL,DB,SUA',
            'Book.*.UsefulLifeMonths' => 'nullable|integer|min:0',
            'Book.*.ResidualPct' => 'nullable|numeric|min:0|max:100',
            'Book.*.DepStartDate' => 'nullable|date',
        ]);

        DB::transaction(function () use ($data, $request) {
            $asset = Asset::create($data);
            foreach (($request->input('Book', []) ?? []) as $row) {
                if (! empty($row['BookID'])) {
                    AssetBookValue::create([
                        'AssetID' => $asset->Id,
                        'BookID' => $row['BookID'],
                        'AcquisitionCost' => $row['AcquisitionCost'] ?? 0,
                        'DepMethod' => $row['DepMethod'] ?? null,
                        'UsefulLifeMonths' => $row['UsefulLifeMonths'] ?? null,
                        'ResidualPct' => $row['ResidualPct'] ?? null,
                        'DepStartDate' => $row['DepStartDate'] ?? null,
                        'AccumDep' => 0,
                        'NBV' => $row['AcquisitionCost'] ?? 0,
                    ]);
                }
            }
            AssetHistory::create([
                'AssetID' => $asset->Id,'EventType' => 'Created','EventDate' => now(),'Reference' => $asset->AssetCode,
                'Remarks' => 'Asset created via Master Data','CreatedOn' => now(),
            ]);
        });

        return redirect()->route('assets.master.register.index')->with('success', 'Asset created.');
    }

    public function show(int $id)
    {
        $asset = Asset::findOrFail($id);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = \App\Models\Assets\Settings\AssetLocation::orderBy('Site')->get();
        $books = AssetBook::orderBy('Name')->get();

        $bookValues = AssetBookValue::where('AssetID', $id)->get();
        $components = \App\Models\Assets\Master\AssetComponent::where('AssetID', $id)->orderBy('ComponentName')->get();
        $meters = \App\Models\Assets\Master\AssetMeter::where('AssetID', $id)->orderBy('MeterName')->get();
        $attachments = \App\Models\Assets\Master\AssetAttachment::where('AssetID', $id)->orderByDesc('UploadedOn')->get();
        $history = \App\Models\Assets\Master\AssetHistory::where('AssetID', $id)->orderByDesc('EventDate')->limit(50)->get();
        $calibrations = \App\Models\Assets\Master\AssetCalibrationEvent::where('AssetID', $id)->orderByDesc('CalibrationDate')->get();
        $providers = AssetServiceProvider::orderBy('Name')->get();

        return view('assets.master.register.show', compact(
            'asset',
            'classes',
            'locations',
            'books',
            'bookValues',
            'components',
            'meters',
            'attachments',
            'history',
            'calibrations',
            'providers'
        ));
    }

    public function edit(int $id)
    {
        $asset = Asset::findOrFail($id);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = \App\Models\Assets\Settings\AssetLocation::orderBy('Site')->get();

        return view('assets.master.register.edit', compact('asset', 'classes', 'locations'));
    }

    public function update(Request $request, int $id)
    {
        $asset = Asset::findOrFail($id);
        $data = $request->validate([
            'AssetCode' => 'required|max:50|unique:t_Assets,AssetCode,' . $asset->Id . ',Id',
            'AssetName' => 'required|max:200',
            'ClassID' => 'required|integer|exists:t_FixedAssetClasses,Id',
            'LocationID' => 'nullable|integer|exists:t_AssetLocations,Id',
            'AcquisitionDate' => 'nullable|date',
            'CapitalizationDate' => 'nullable|date',
            'Status' => 'required|in:Active,Inactive,Disposed',
            'Manufacturer' => 'nullable|max:120',
            'Model' => 'nullable|max:120',
            'SerialNumber' => 'nullable|max:120',
            'TagNo' => 'nullable|max:120',
            'Notes' => 'nullable|string',
        ]);
        $asset->update($data);

        AssetHistory::create([
            'AssetID' => $asset->Id,'EventType' => 'Edited','EventDate' => now(),
            'Reference' => $asset->AssetCode,'Remarks' => 'Profile updated','CreatedOn' => now(),
        ]);

        return redirect()->route('assets.master.register.show', $asset->Id)->with('success', 'Asset updated.');
    }

    public function destroy(int $id)
    {
        Asset::where('Id', $id)->delete();

        return redirect()->route('assets.master.register.index')->with('success', 'Asset deleted.');
    }
}
