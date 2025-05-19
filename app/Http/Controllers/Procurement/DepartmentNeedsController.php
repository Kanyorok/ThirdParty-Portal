<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\Item;
use App\Services\Procurement\ProcurementPlan\DepartmentNeedsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Throwable;

class DepartmentNeedsController extends Controller
{
    protected DepartmentNeedsService $service;

    public function __construct(DepartmentNeedsService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        $items = Item::with('category')->orderBy('ItemName')->get(['Id', 'ItemName as Name', 'UOM']);
        return view('procurement.procurementplan.departmentneeds.raiseneed.create', compact('items'));
    }

    public function store(Request $request, DepartmentNeedsService $service)
    {
        //dd($request->user()->employee);

        try {
            DB::transaction(function () use ($request, $service) {
                $actor = $request->user();
                $service->create($request->all(), $actor);
            });

            return redirect()->back()->with('success', 'Department need created!');
        } catch (Throwable $e) {
            Log::error("--- CREATE DEPARTMENT NEEDS ERROR --- " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create need.']);
        }
    }

    public function index(Request $request)
    {
        // if ($request->ajax()) {
        //     try {
        //         // ✅ Test data comes through
        //         $data = DepartmentNeeds::with('item.category')->get();
        //         \Log::info('DepartmentNeeds AJAX data:', $data->toArray());

        //         // ✅ Pass eager-loaded data to DataTables
        //         return DataTables::of(DepartmentNeeds::with(['item.category']))
        //             ->addIndexColumn()
        //             ->addColumn('item_name', fn($row) => $row->item->ItemName ?? 'N/A')
        //             ->addColumn('category', fn($row) => $row->item->category->Name ?? 'N/A')
        //             ->addColumn('quantity', fn($row) => $row->RequestedQty)
        //             ->addColumn('estimated_cost', fn($row) => number_format($row->EstimatedUnitCost, 2))
        //             ->addColumn('status', fn($row) => '<span class="badge bg-warning">'.Str::title($row->Status).'</span>')
        //             ->addColumn('required_by', fn($row) => '-') // Replace with actual date if available
        //             ->addColumn('action', function ($row) {
        //                 return '<a href="' . route('procurementdepartmentalplan.show', $row->Id) . '" class="btn btn-sm btn-outline-info">View</a>
        //                         <a href="' . route('procurementdepartmentalplan.edit', $row->Id) . '" class="btn btn-sm btn-outline-primary">Edit</a>';
        //             })
        //             ->rawColumns(['status', 'action'])
        //             ->make(true);

        //     } catch (\Exception $e) {
        //         \Log::error('DepartmentNeeds fetch error: ' . $e->getMessage());
        //         return response()->json(['error' => 'Could not fetch records.'], 500);
        //     }
        // }

        $departmentneedviews = DepartmentNeeds::all();
        $departmentneedviews = DepartmentNeeds::with('creator')->get();
        return view('procurement.procurementplan.departmentneeds.raiseneed.index', compact('departmentneedviews'));
        // return view('procurement.procurementplan.departmentneeds.raiseneed.index');
    }



}
