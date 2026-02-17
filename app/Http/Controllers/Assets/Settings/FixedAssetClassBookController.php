<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\AssetBook;
use App\Models\Assets\Settings\FixedAssetClass;
use App\Models\Assets\Settings\FixedAssetClassBook;
use Illuminate\Http\Request;

class FixedAssetClassBookController extends Controller
{
    public function index(Request $request)
    {
        $rows = FixedAssetClassBook::orderBy('Id', 'desc')->paginate(20);
        $classes = FixedAssetClass::orderBy('Name')->get(['Id','Name','Code']);
        $books = AssetBook::orderBy('Name')->get(['Id','Name','Code']);

        return view('assets.settings.classbooks.index', compact('rows', 'classes', 'books'));
    }

    public function create()
    {
        $classes = FixedAssetClass::orderBy('Name')->get();
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.settings.classbooks.create', compact('classes', 'books'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ClassID' => 'required|integer|exists:t_FixedAssetClasses,Id',
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'DepMethod' => 'nullable|in:SL,DB,SUA',
            'UsefulLifeMonths' => 'nullable|integer|min:0',
            'ResidualPct' => 'nullable|numeric|min:0|max:100',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        FixedAssetClassBook::create($data);

        return redirect()->route('assets.settings.class-books.index')->with('success', 'Class-Book override created.');
    }

    public function edit(int $id)
    {
        $row = FixedAssetClassBook::findOrFail($id);
        $classes = FixedAssetClass::orderBy('Name')->get();
        $books = AssetBook::orderBy('Name')->get();

        return view('assets.settings.classbooks.edit', compact('row', 'classes', 'books'));
    }

    public function update(Request $request, int $id)
    {
        $row = FixedAssetClassBook::findOrFail($id);
        $data = $request->validate([
            'ClassID' => 'required|integer|exists:t_FixedAssetClasses,Id',
            'BookID' => 'required|integer|exists:t_AssetBooks,Id',
            'DepMethod' => 'nullable|in:SL,DB,SUA',
            'UsefulLifeMonths' => 'nullable|integer|min:0',
            'ResidualPct' => 'nullable|numeric|min:0|max:100',
            'IsActive' => 'nullable|boolean',
        ]);
        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);

        return redirect()->route('assets.settings.class-books.index')->with('success', 'Class-Book override updated.');
    }

    public function destroy(int $id)
    {
        FixedAssetClassBook::where('Id', $id)->delete();

        return redirect()->route('assets.settings.class-books.index')->with('success', 'Class-Book override deleted.');
    }
}
