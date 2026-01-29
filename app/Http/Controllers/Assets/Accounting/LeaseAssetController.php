<?php

namespace App\Http\Controllers\Assets\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Assets\Accounting\LeaseAsset;
use App\Models\Assets\Master\Asset;
use App\Models\Assets\Settings\{AssetBook};
use Illuminate\Http\Request;

class LeaseAssetController extends Controller
{
    public function index()
    {
        $rows = LeaseAsset::orderByDesc('CreatedOn')->paginate(20);

        return view('assets.acc.lease.index', compact('rows'));
    }

    public function create()
    {
        $books = AssetBook::orderBy('Name')->get();
        $assets = Asset::orderBy('AssetCode')->limit(200)->get(['Id','AssetCode','AssetName']);

        return view('assets.acc.lease.create', compact('books', 'assets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'AssetID' => 'required|integer|exists:t_Assets,Id',
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'LeaseCode' => 'required|max:50|unique:t_LeaseAssets,LeaseCode',
            'Commencement' => 'required|date',
            'TermMonths' => 'required|integer|min:1',
            'DiscountRatePA' => 'required|numeric|min:0',
            'PaymentAmount' => 'required|numeric|min:0',
            'PaymentFreq' => 'required|in:MONTH,QUARTER',
        ]);
        LeaseAsset::create($data);

        return redirect()->route('assets.acc.lease.index')->with('success', 'Lease saved.');
    }
}
