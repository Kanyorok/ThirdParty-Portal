<?php
namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\{FixedAssetClass, AssetLocation, AssetBook};
use App\Models\Assets\Master\{Asset, AssetBookValue, AssetHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AssetNumbering;

class DirectCapitalizationController extends Controller
{
    public function create()
    {
        $classes = FixedAssetClass::orderBy('Name')->get();
        $locations = \App\Models\Assets\Settings\AssetLocation::orderBy('Site')->get();
        $books = \App\Models\Assets\Settings\AssetBook::orderBy('Name')->get();
        return view('assets.acq.direct.create', compact('classes','locations','books'));
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'AssetCode' => 'nullable|max:50',
        'AssetName' => 'required|max:200',
        'ClassID'   => 'required|integer|exists:t_FixedAssetClasses,Id',
        'LocationID'=> 'nullable|integer|exists:t_AssetLocations,Id',
        'BookID'    => 'required|integer|exists:t_AssetBooks,Id',
        'DepStartDate' => 'nullable|date',
        'AcquisitionCost' => 'required|numeric|min:0.01',
        'ResidualPct'     => 'nullable|numeric|min:0|max:100'
    ]);

    $class = FixedAssetClass::findOrFail($data['ClassID']);
    $threshold = (float)($class->CapThreshold ?? 0);
    if ($threshold > 0 && (float)$data['AcquisitionCost'] < $threshold) {
        return back()->withErrors('Direct capitalization blocked by policy: amount is below class capitalization threshold ('.number_format($threshold,2).').')->withInput();
    }

    $num = new AssetNumbering(); // <— use service

    DB::transaction(function() use ($data, $num) {
        $code = $data['AssetCode'] ?: $num->nextAssetCode([
            'ClassID'    => $data['ClassID'],
            'LocationID' => $data['LocationID'] ?? null,
            'BookID'     => $data['BookID'],
            'Date'       => $data['DepStartDate'] ?? now()->toDateString(),
        ]);

        $asset = Asset::create([
            'AssetCode'=>$code,'AssetName'=>$data['AssetName'],'ClassID'=>$data['ClassID'],
            'LocationID'=>$data['LocationID'] ?? null,'Status'=>'Active',
            'CapitalizationDate'=>$data['DepStartDate'] ?? null,'IsActive'=>1
        ]);

        AssetBookValue::create([
            'AssetID'=>$asset->Id,'BookID'=>$data['BookID'],'AcquisitionCost'=>$data['AcquisitionCost'],
            'ResidualPct'=>$data['ResidualPct'] ?? null,'DepStartDate'=>$data['DepStartDate'] ?? null,
            'AccumDep'=>0,'NBV'=>$data['AcquisitionCost']
        ]);

        AssetHistory::create([
            'AssetID'=>$asset->Id,'EventType'=>'Capitalized','EventDate'=>now(),
            'Reference'=>'DIRECT','Remarks'=>'Direct capitalization','CreatedOn'=>now()
        ]);
    });

    return redirect()->route('assets.master.register.index')->with('success','Asset capitalized directly.');
}
}