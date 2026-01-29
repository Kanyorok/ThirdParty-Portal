<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\AssetServiceProvider;
use Illuminate\Http\Request;

class AssetServiceProviderController extends Controller
{
    /** Optional set for dropdowns in the form */
    private array $categories = ['Maintenance','Insurance','Valuation','Calibration','Other'];

    public function index(Request $request)
    {
        $q = $request->get('q');
        $cat = $request->get('category');

        $rows = AssetServiceProvider::when($q, fn ($qq) =>
                        $qq->where('Name', 'like', "%$q%")
                           ->orWhere('ContactEmail', 'like', "%$q%")
                           ->orWhere('ContactPhone', 'like', "%$q%"))
                    ->when($cat, fn ($qq) => $qq->where('Category', $cat))
                    ->orderBy('Name')->paginate(20);

        return view('assets.settings.serviceproviders.index', [
            'rows' => $rows,
            'q' => $q,
            'category' => $cat,
            'categories' => $this->categories,
        ]);
    }

    public function create()
    {
        $categories = $this->categories;

        return view('assets.settings.serviceproviders.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => 'required|max:150|unique:t_AssetServiceProviders,Name',
            'Category' => 'nullable|max:50',
            'SupplierID' => 'nullable|integer',
            'ContactEmail' => 'nullable|email|max:120',
            'ContactPhone' => 'nullable|max:50',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        AssetServiceProvider::create($data);

        return redirect()->route('assets.settings.service-providers.index')
            ->with('success', 'Service provider created.');
    }

    public function edit(int $id)
    {
        $row = AssetServiceProvider::findOrFail($id);
        $categories = $this->categories;

        return view('assets.settings.serviceproviders.edit', compact('row', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        $row = AssetServiceProvider::findOrFail($id);

        $data = $request->validate([
            'Name' => 'required|max:150|unique:t_AssetServiceProviders,Name,' . $row->Id . ',Id',
            'Category' => 'nullable|max:50',
            'SupplierID' => 'nullable|integer',
            'ContactEmail' => 'nullable|email|max:120',
            'ContactPhone' => 'nullable|max:50',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);

        return redirect()->route('assets.settings.service-providers.index')
            ->with('success', 'Service provider updated.');
    }

    public function destroy(int $id)
    {
        AssetServiceProvider::where('Id', $id)->delete();

        return redirect()->route('assets.settings.service-providers.index')
            ->with('success', 'Service provider deleted.');
    }

    public function show(int $id)
    {
        $row = \App\Models\Assets\Settings\AssetServiceProvider::findOrFail($id);
        $categories = ['Maintenance','Insurance','Valuation','Calibration','Other'];

        return view('assets.settings.serviceproviders.show', compact('row', 'categories'));
    }
}
