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
use App\Services\ThirdParties\SupplierService;

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
                    'prequalificationApplications.category.itemCategories',
                    'supplierMaster'
                ])
                ->select([
                    't_ThirdParties.Id',
                    't_ThirdParties.ThirdPartyName',
                    't_ThirdParties.TradingName',
                    't_ThirdParties.Email',
                ])
                ->addSelect([
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
                        ->orWhere('Email', 'like', "%{$searchValue}%");
                });
            }

            return DataTables::of($query)
                ->addColumn('ThirdPartyType', function ($supplier) {
                    $codes = $supplier->types->pluck('Code')->filter()->unique();
                    return $codes->isNotEmpty() ? $codes->join(', ') : 'Supplier';
                })
                ->addColumn('ApprovalStatus', function ($supplier) {
                    $master = $supplier->supplierMaster;
                    if ($master && $master->ApprovalStatus) {
                        return method_exists($master->ApprovalStatus, 'label')
                            ? $master->ApprovalStatus->label()
                            : $master->ApprovalStatus;
                    }
                    return 'Pending';
                })
                ->addColumn('Prequalified', function ($supplier) {
                    return ($supplier->supplierMaster->IsPrequalified ?? false) ? 'Yes' : 'No';
                })
                ->addColumn('category_names', function ($supplier) {
                    $isPrequalified = $supplier->supplierMaster->IsPrequalified ?? false;
                    if (!$isPrequalified) {
                        return '<span class="text-muted">Not prequalified</span>';
                    }

                    $newCats = $supplier->categories ?? collect();
                    $appCats = ($supplier->prequalificationApplications ?? collect())
                        ->pluck('category')
                        ->filter()
                        ->unique('SupplierCategoryID');

                    $legacyCats = ($supplier->legacyCategories ?? collect())->map(function ($map) {
                        return (object) [
                            'CategoryName' => $map->category->Name ?? $map->category->Description ?? 'Category',
                            'itemCategories' => collect(),
                        ];
                    });

                    $merged = $newCats->concat($appCats)->concat($legacyCats)
                        ->map(function ($cat) {
                            $cat->CategoryName = $cat->CategoryName ?? 'Category';
                            return $cat;
                        })
                        ->unique(function ($c) {
                            return isset($c->SupplierCategoryID) ? 'id-' . $c->SupplierCategoryID : 'name-' . strtolower($c->CategoryName);
                        });

                    if ($merged->isEmpty()) {
                        return '<span class="text-warning">No categories assigned</span>';
                    }

                    $html = '<dl class="mb-0">';
                    foreach ($merged as $cat) {
                        $catName = e($cat->CategoryName);
                        $itemCats = $cat->itemCategories ?? collect();
                        $count = $itemCats->count();
                        $badge = $count > 0 ? " <span class=\"badge bg-secondary ms-1\">{$count}</span>" : '';
                        $itemList = $count > 0 ? e($itemCats->pluck('Name')->filter()->unique()->implode(', ')) : 'No specific items';
                        $html .= "<dt class=\"fw-semibold\">{$catName}{$badge}</dt><dd class=\"mb-1\">{$itemList}</dd>";
                    }
                    return $html . '</dl>';
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
                    return '
                    <div class="d-flex gap-1">
                        <a href="' . route('suppliers.show', $supplier->Id) . '" class="btn btn-sm btn-info">View</a>
                        <a href="' . route('suppliers.edit', $supplier->Id) . '" class="btn btn-sm btn-warning">Edit</a>
                        <form action="' . route('suppliers.destroy', $supplier->Id) . '" method="POST" class="inline-block">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger delete-btn">Delete</button>
                        </form>
                    </div>';
                })
                ->rawColumns(['actions', 'category_names'])
                ->make(true);
        }

        return view('procurement.suppliers.index', ['categories' => SupplierCategory::all()]);
    }

    public function create()
    {
        return view('procurement.suppliers.create', ['categories' => SupplierCategory::all()]);
    }

    public function store(StoreSupplierRequest $request)
    {
        DB::transaction(function () use ($request) {
            $party = ThirdParties::create(array_merge($request->validated(), ['CreatedBy' => Auth::id()]));
            SupplierService::createFromParty($party, Auth::user());
            if ($request->has('category_ids')) {
                $party->categories()->sync($request->category_ids);
            }
        });

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(ThirdParties $supplier)
    {
        $supplier->load('categories', 'types', 'supplierMaster');
        return view('procurement.suppliers.show', compact('supplier'));
    }

    public function edit(ThirdParties $supplier)
    {
        $supplier->load('categories', 'types', 'supplierMaster');
        return view('procurement.suppliers.edit', ['supplier' => $supplier, 'categories' => SupplierCategory::all()]);
    }

    public function update(UpdateSupplierRequest $request, ThirdParties $supplier)
    {
        $supplier->update(array_merge($request->validated(), ['ModifiedBy' => Auth::id()]));
        $supplier->categories()->sync($request->input('category_ids', []));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(ThirdParties $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
