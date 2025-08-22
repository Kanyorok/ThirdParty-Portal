<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\SupplierCategory;
use App\Models\ThirdParty\ThirdParties;
use App\Enums\ThirdPartyTypeEnum;
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
            $query = ThirdParties::suppliers()->with('categories');

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
                    return $supplier->ThirdPartyType->label();
                })
                ->addColumn('ApprovalStatus', function ($supplier) {
                    return $supplier->ApprovalStatus->label();
                })
                ->addColumn('Prequalified', function ($supplier) {
                    return $supplier->IsPrequalified ? 'Yes' : 'No';
                })
                ->addColumn('category_names', function ($supplier) {
                    return $supplier->categories->pluck('CategoryName')->implode(', ');
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
                ->rawColumns(['actions'])
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
        $validatedData['ThirdPartyType'] = ThirdPartyTypeEnum::Supplier;
        $validatedData['CreatedBy'] = Auth::id();

        $supplier = ThirdParties::create($validatedData);
        $supplier->categories()->sync($request->input('category_ids', []));

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(ThirdParties $supplier)
    {
        $supplier->load('categories');
        return view('procurement.suppliers.show', compact('supplier'));
    }

    public function edit(ThirdParties $supplier)
    {
        $categories = SupplierCategory::all();
        $supplier->load('categories');
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
