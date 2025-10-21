<?php
namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\FixedAssetClass;
use Illuminate\Http\Request;

class FixedAssetClassController extends Controller
{
    public function index(Request $request) {
        $q = $request->get('q');
        $rows = FixedAssetClass::when($q, fn($qq) =>
                $qq->where('Code','like',"%$q%")
                   ->orWhere('Name','like',"%$q%"))
            ->orderBy('Name')->paginate(20);
        return view('assets.settings.classes.index', compact('rows','q'));
    }

    public function create() { return view('assets.settings.classes.create'); }

    public function store(Request $request) {
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_FixedAssetClasses,Code',
            'Name' => 'required|max:150',
            'DepMethod' => 'required|in:SL,DB,SUA',
            'UsefulLifeMonths' => 'nullable|integer|min:0',
            'ResidualPct' => 'required|numeric|min:0|max:100',
            'CapThreshold' => 'required|numeric|min:0',
            'PoolingFlag' => 'nullable|boolean',
            'RevaluationAllowed' => 'nullable|boolean',
            'DefaultGLMap' => 'nullable|string',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['PoolingFlag'] = $request->boolean('PoolingFlag');
        $data['RevaluationAllowed'] = $request->boolean('RevaluationAllowed');
        $data['IsActive'] = $request->boolean('IsActive');
        FixedAssetClass::create($data);
        return redirect()->route('assets.settings.classes.index')->with('success','Class created.');
    }

    public function edit(int $id) {
        $row = FixedAssetClass::findOrFail($id);
        return view('assets.settings.classes.edit', compact('row'));
    }

    public function update(Request $request, int $id) {
        $row = FixedAssetClass::findOrFail($id);
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_FixedAssetClasses,Code,'.$row->Id.',Id',
            'Name' => 'required|max:150',
            'DepMethod' => 'required|in:SL,DB,SUA',
            'UsefulLifeMonths' => 'nullable|integer|min:0',
            'ResidualPct' => 'required|numeric|min:0|max:100',
            'CapThreshold' => 'required|numeric|min:0',
            'PoolingFlag' => 'nullable|boolean',
            'RevaluationAllowed' => 'nullable|boolean',
            'DefaultGLMap' => 'nullable|string',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['PoolingFlag'] = $request->boolean('PoolingFlag');
        $data['RevaluationAllowed'] = $request->boolean('RevaluationAllowed');
        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);
        return redirect()->route('assets.settings.classes.index')->with('success','Class updated.');
    }

    public function destroy(int $id) {
        FixedAssetClass::where('Id',$id)->delete();
        return redirect()->route('assets.settings.classes.index')->with('success','Class deleted.');
    }
}
