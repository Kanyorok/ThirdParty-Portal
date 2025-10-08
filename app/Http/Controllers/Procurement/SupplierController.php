<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierCategory;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Procurement\Suppliers\Prequalification\StoreSupplierRequest;
use App\Http\Requests\Procurement\Suppliers\Prequalification\UpdateSupplierRequest;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ThirdParties::suppliers()
                ->with([
                    'categories.itemCategories',
                    'types',
                    'legacyCategories.category',
                    'prequalificationApplications.category'
                ])
                ->select([
                    'Id',
                    'ThirdPartyName',
                    'TradingName',
                    'ApprovalStatus',
                    'IsPrequalified',
                    'Email',
                ])
                ->addSelect([
                    // Primary contact derived from latest ThirdPartyUser by CreatedOn
                    'PrimaryFirstName' => DB::table('t_ThirdPartyUsers')
                        ->select('FirstName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'PrimaryLastName' => DB::table('t_ThirdPartyUsers')
                        ->select('LastName')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                    'PrimaryEmail' => DB::table('t_ThirdPartyUsers')
                        ->select('Email')
                        ->whereColumn('t_ThirdPartyUsers.ThirdPartyId', 't_ThirdParties.Id')
                        ->orderByDesc('CreatedOn')
                        ->limit(1),
                ]);

            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function ($q) use ($searchValue) {
                    $q->where('ThirdPartyName', 'like', "%{$searchValue}%")
                        ->orWhere('TradingName', 'like', "%{$searchValue}%")
                        ->orWhere('Email', 'like', "%{$searchValue}%")
                        ->orWhere('Phone', 'like', "%{$searchValue}%");
                });
            }

            if ($request->filled('status')) {
                $statusValue = $request->input('status');
                if ($statusValue !== '') {
                    $query->where('ApprovalStatus', $statusValue);
                }
            }

            return DataTables::of($query)
                ->addColumn('ThirdPartyType', function ($supplier) {
                    $codes = $supplier->types->pluck('Code')->filter()->unique();
                    return $codes->isNotEmpty() ? $codes->join(', ') : 'Supplier';
                })
                ->addColumn('ApprovalStatus', function ($supplier) {
                    return $supplier->ApprovalStatus->label();
                })
                ->addColumn('Prequalified', function ($supplier) {
                    return $supplier->IsPrequalified ? 'Yes' : 'No';
                })
                ->addColumn('category_names', function ($supplier) {
                    if (!$supplier->IsPrequalified) {
                        return '<span class="text-muted">Not prequalified</span>';
                    }

                    // Collect new pivot categories first
                    $newCats = $supplier->categories ?? collect();

                    // Categories from prequalification applications (each application has a single category)
                    $appCats = ($supplier->prequalificationApplications ?? collect())
                        ->pluck('category')
                        ->filter()
                        ->map(function ($cat) {
                            return (object) [
                                'CategoryName' => $cat->CategoryName ?? 'Category',
                                'itemCategories' => collect(),
                            ];
                        });

                    // Map legacy categories to synthetic objects (only if legacy exists and not already represented)
                    $legacyCats = ($supplier->legacyCategories ?? collect())->map(function ($map) {
                        $label = $map->category->Name ?? $map->category->Description ?? 'Category';
                        return (object) [
                            'CategoryName' => $label,
                            'itemCategories' => collect(),
                        ];
                    });

                    // Merge ensuring uniqueness by CategoryName
                    $merged = $newCats->map(function ($cat) {
                        // Normalize to common shape
                        $cat->CategoryName = $cat->CategoryName ?? 'Category';
                        return $cat;
                    })
                        ->concat($appCats)
                        ->concat($legacyCats)
                        ->unique(fn($c) => strtolower($c->CategoryName));

                    if ($merged->isEmpty()) {
                        return '<span class="text-warning">No categories assigned</span>';
                    }

                    $html = '<dl class="mb-0">';
                    foreach ($merged as $cat) {
                        $catName = e($cat->CategoryName ?? 'Category');
                        $itemCats = $cat->itemCategories ?? collect();
                        $count = $itemCats->count();
                        $badge = $count > 0 ? " <span class=\"badge bg-secondary ms-1\">{$count}</span>" : '';
                        $itemList = $count > 0
                            ? e($itemCats->pluck('Name')->filter()->unique()->implode(', '))
                            : 'No specific items';
                        $html .= "<dt class=\"fw-semibold\">{$catName}{$badge}</dt><dd class=\"mb-1\">{$itemList}</dd>";
                    }
                    $html .= '</dl>';
                    return $html;
                })
                ->addColumn('TradingName', function ($supplier) {
                    return $supplier->TradingName ?? 'N/A';
                })
                ->addColumn('PrimaryContact', function ($supplier) {
                    $full = trim(($supplier->PrimaryFirstName ?? '') . ' ' . ($supplier->PrimaryLastName ?? ''));
                    return $full !== '' ? $full : 'N/A';
                })
                ->addColumn('PrimaryEmail', function ($supplier) {
                    return $supplier->PrimaryEmail ?? $supplier->Email ?? 'N/A';
                })
                ->addColumn('actions', function ($supplier) {
                    $viewUrl = route('suppliers.show', $supplier->Id);
                    $editUrl = route('suppliers.edit', $supplier->Id);
                    $deleteUrl = route('suppliers.destroy', $supplier->Id);

                    return '
                    <div class="d-flex gap-1">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-info">View</a>
                        <a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>
                        <form action="' . $deleteUrl . '" method="POST" class="inline-block">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger delete-btn">Delete</button>
                        </form>
                    </div>
                ';
                })
                ->rawColumns(['actions','category_names'])
                ->make(true);
        }

        $categories = SupplierCategory::all();
        return view('procurement.suppliers.index', compact('categories'));
    }

    public function create()
    {
        $categories = SupplierCategory::all();
        return view('procurement.suppliers.create', compact('categories'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['CreatedBy'] = Auth::id();

        $supplier = ThirdParties::create($validatedData);
        // Attach supplier type via pivot (Code like SU-%). Pick first matching type.
        $supplierTypeId = DB::table('t_ThirdPartyTypes')->where('Code','like','SU-%')->value('TypeId');
        if ($supplierTypeId) {
            DB::table('t_ThirdPartyType_ThirdParties')->insert([
                'TypeId' => $supplierTypeId,
                'ThirdPartyId' => $supplier->Id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }
        $supplier->categories()->sync($request->input('category_ids', []));

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(ThirdParties $supplier)
    {
    $supplier->load('categories','types');
        return view('procurement.suppliers.show', compact('supplier'));
    }

    public function edit(ThirdParties $supplier)
    {
        $categories = SupplierCategory::all();
    $supplier->load('categories','types');
        return view('procurement.suppliers.edit', compact('supplier', 'categories'));
    }

    public function update(UpdateSupplierRequest $request, ThirdParties $supplier)
    {
        $validatedData = $request->validated();
        $validatedData['ModifiedBy'] = Auth::id();

        $supplier->update($validatedData);
        $supplier->categories()->sync($request->input('category_ids', []));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(ThirdParties $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
