<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\AssetNumberingRule;
use Illuminate\Http\Request;

class AssetNumberingRuleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = AssetNumberingRule::when($q, fn($qq) =>
                        $qq->where('Prefix','like',"%$q%")
                           ->orWhere('Separator','like',"%$q%")
                           ->orWhere('SamplePreview','like',"%$q%"))
                    ->orderBy('Id','desc')->paginate(20);

        return view('assets.settings.numberingrules.index', compact('rows','q'));
    }

    public function create()
    {
        return view('assets.settings.numberingrules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Prefix'        => 'nullable|max:20',
            'Separator'     => 'required|max:5',
            'Padding'       => 'required|integer|min:1|max:12',
            'NextNumber'    => 'required|integer|min:1',
            'SamplePreview' => 'nullable|max:50',
            'IsActive'      => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        if (empty($data['SamplePreview'])) {
            $data['SamplePreview'] = $this->preview($data['Prefix'] ?? null, $data['Separator'], $data['Padding'], $data['NextNumber']);
        }

        AssetNumberingRule::create($data);
        return redirect()->route('assets.settings.numbering-rules.index')->with('success','Numbering rule created.');
    }

    public function show(int $id)
    {
        $row = AssetNumberingRule::findOrFail($id);
        return view('assets.settings.numberingrules.show', compact('row'));
    }

    public function edit(int $id)
    {
        $row = AssetNumberingRule::findOrFail($id);
        return view('assets.settings.numberingrules.edit', compact('row'));
    }

    public function update(Request $request, int $id)
    {
        $row = AssetNumberingRule::findOrFail($id);

        $data = $request->validate([
            'Prefix'        => 'nullable|max:20',
            'Separator'     => 'required|max:5',
            'Padding'       => 'required|integer|min:1|max:12',
            'NextNumber'    => 'required|integer|min:1',
            'SamplePreview' => 'nullable|max:50',
            'IsActive'      => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        if (empty($data['SamplePreview'])) {
            $data['SamplePreview'] = $this->preview($data['Prefix'] ?? null, $data['Separator'], $data['Padding'], $data['NextNumber']);
        }

        $row->update($data);
        return redirect()->route('assets.settings.numbering-rules.index')->with('success','Numbering rule updated.');
    }

    public function destroy(int $id)
    {
        AssetNumberingRule::where('Id',$id)->delete();
        return redirect()->route('assets.settings.numbering-rules.index')->with('success','Numbering rule deleted.');
    }

    private function preview(?string $prefix, string $sep, int $pad, int $num): string
    {
        $body = str_pad((string)$num, $pad, '0', STR_PAD_LEFT);
        return ($prefix ? $prefix.$sep : '').$body;
    }
}
