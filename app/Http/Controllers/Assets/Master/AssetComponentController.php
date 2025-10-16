<?php
namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\AssetComponent;
use Illuminate\Http\Request;

class AssetComponentController extends Controller
{
    public function index(int $asset)
    {
        $rows = AssetComponent::where('AssetID',$asset)->orderBy('ComponentName')->paginate(20);
        return view('assets.master.components.index', compact('rows','asset'));
    }

    public function create(int $asset)
    { return view('assets.master.components.create', compact('asset')); }

    public function store(Request $request, int $asset)
    {
        $data = $request->validate([
            'ComponentName' => 'required|max:200',
            'SerialNumber'  => 'nullable|max:120',
            'Quantity'      => 'required|integer|min:1',
            'AcquisitionDate' => 'nullable|date',
            'Cost'          => 'nullable|numeric|min:0',
            'IsCritical'    => 'nullable|boolean',
        ]);
        $data['AssetID'] = $asset;
        $data['IsCritical'] = $request->boolean('IsCritical');
        AssetComponent::create($data);
        return back()->with('success','Component added.');
    }

    public function edit(int $asset, int $id)
    {
        $row = AssetComponent::where('AssetID',$asset)->findOrFail($id);
        return view('assets.master.components.edit', compact('asset','row'));
    }

    public function update(Request $request, int $asset, int $id)
    {
        $row = AssetComponent::where('AssetID',$asset)->findOrFail($id);
        $data = $request->validate([
            'ComponentName' => 'required|max:200',
            'SerialNumber'  => 'nullable|max:120',
            'Quantity'      => 'required|integer|min:1',
            'AcquisitionDate' => 'nullable|date',
            'Cost'          => 'nullable|numeric|min:0',
            'IsCritical'    => 'nullable|boolean',
        ]);
        $data['IsCritical'] = $request->boolean('IsCritical');
        $row->update($data);
        return back()->with('success','Component updated.');
    }

    public function destroy(int $asset, int $id)
    {
        AssetComponent::where('AssetID',$asset)->where('Id',$id)->delete();
        return back()->with('success','Component deleted.');
    }
}
