<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockConsumptionRequest;
use App\Models\Inventory\StockConsumption;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use App\Models\Inventory\StockItem;
use App\Models\Auth\User;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\HRM\Employee;
use App\Models\HRM\Department;
use App\Models\Core\CodeDetail;
use App\Services\Inventory\StockConsumptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockConsumptionController extends Controller
{
    protected StockConsumptionService $stockConsumptionService;

    public function __construct(StockConsumptionService $stockConsumptionService)
    {
        $this->stockConsumptionService = $stockConsumptionService;
    }

    public function index()
    {
        $consumptions = StockConsumption::with(['item', 'store', 'uom', 'issuedBy', 'branch', 'stockItem'])->get();
        return view('inventory.stockmanagement.stockconsumption.index', compact('consumptions'));
    }

    public function create()
    {
        $branchId = auth()->user()->employee?->BranchId;
        $branch = Branch::findOrFail($branchId);

        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        $stores = Store::where('BranchID', $branchId)->get();
        $items = StockItem::all();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);

        return view('inventory.stockmanagement.stockconsumption.create', compact('branch', 'stores', 'items', 'uoms', 'types', 'users'));
    }

    public function store(StockConsumptionRequest $request)
    {
        try {
            $this->stockConsumptionService->create($request->validated());
            return redirect()->route('stockconsumption.index')->with('success', 'Stock consumption recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to record stock consumption: ' . $e->getMessage())->withInput();
        }
    }
    public function edit($id)
    {
        $consumption = StockConsumption::with(['item', 'store', 'uom', 'issuedBy', 'branch'])->findOrFail($id);

        $branchId = auth()->user()->employee?->BranchId;
        $branch = Branch::findOrFail($branchId);

        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        $stores = Store::where('BranchID', $branchId)->get();
        $items = StockItem::where('Branch', $branchId)->get();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);

        return view('inventory.stockmanagement.stockconsumption.edit', compact(
            'consumption', 'branch', 'stores', 'items', 'uoms', 'types', 'users'
        ));
    }

    public function update(StockConsumptionRequest $request, $id)
    {
        try {
            $consumption = StockConsumption::findOrFail($id);
            $this->stockConsumptionService->update($consumption, $request->validated());

            return redirect()->route('stockconsumption.index')
                ->with('success', 'Stock consumption updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update stock consumption: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $consumption = StockConsumption::with([
            'item',
            'store',
            'uom',
            'issuedBy',
            'branch',
            'stockItem'
        ])->findOrFail($id);

        return view('inventory.stockmanagement.stockconsumption.show', compact('consumption'));
    }




    public function getStores(Request $request)
    {
        $branchId = auth()->user()->employee?->BranchId;
        $stores = Store::where('BranchID', $branchId)->get(['Id', 'StoreName']);
        return response()->json($stores);
    }

    public function getItems(Request $request)
    {
        $branchId = auth()->user()->employee?->BranchId;
        $storeId = $request->get('StoreID');

        $query = StockItem::with(['uom', 'item']);

        if ($storeId) {
            $query->where('Store', $storeId)->where('Branch', $branchId);
        } else {
            $query->where('Branch', $branchId);
        }

        $items = $query->get();

        return response()->json($items->map(function ($item) {
            return [
                'Id' => $item->Id,
                'ItemName' => $item->item?->ItemName ?? '',
                'UOM' => $item->UOM,
                'UOMCode' => $item->uom?->Code ?? '',
                'CurrentQty' => $item->CurrentQty,
            ];
        }));
    }


    public function getIssuedToOptions(Request $request)
    {
        $codeDetail = CodeDetail::find($request->get('type'));
        $type = strtoupper($codeDetail?->Description ?? '');

        switch ($type) {
            case 'EMPLOYEE':
                return Employee::where('BranchId', auth()->user()->employee?->BranchId)
                    ->select('Id', DB::raw("CONCAT(FirstName, ' ', LastName) AS Name"))->get();
            case 'DEPARTMENT':
                return Department::select('Id', 'Name')->get();
            default:
                return response()->json([], 200);
        }
    }

    public function destroy($id)
    {
        $item = StockConsumption::findOrFail($id);

        try {
            $this->stockConsumptionService->delete($item);
            return redirect()->route('stockconsumption.index')->with('success', '🗑️ Stock consumption deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete stock consumption: ' . $e->getMessage());
        }
    }
}
