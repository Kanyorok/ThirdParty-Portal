<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockConsumptionRequest;
use App\Models\Inventory\StockConsumption;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
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
use Illuminate\Support\Facades\Log;


class StockConsumptionController extends Controller
{
    protected StockConsumptionService $stockConsumptionService;

    public function __construct(StockConsumptionService $stockConsumptionService)
    {
        $this->stockConsumptionService = $stockConsumptionService;
    }

    public function index()
    {
        //$this->authorize('viewAny', StockConsumption::class);
        $consumptions = StockConsumption::with(['item','store','uom','issuedBy','branch'])->get();

        return view('inventory.stockmanagement.stockconsumption.index', compact('consumptions'));
    }

    public function create()
    {
        //$this->authorize('create', StockConsumption::class);

        $branches = Branch::all();
        $users = User::all();
        $stores = Store::all();
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);
        $consumptions= StockConsumption::with(['item', 'store', 'uom', 'creator'])->get();

        return view('inventory.stockmanagement.stockconsumption.create', compact('branches', 'items', 'uoms', 'types', 'consumptions','users'));
    }

    public function store(StockConsumptionRequest $request)
    {
        //$this->authorize('create', StockConsumption::class);

        try {
            $this->stockConsumptionService->create($request->validated());
            return redirect()->route('stockconsumption.index')->with('success', 'Stock consumption recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to record stock consumption: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $item = StockConsumption::with(['item', 'store', 'uom'])->findOrFail($id);
        //$this->authorize('view', $item);

        return view('inventory.stockmanagement.stockconsumption.show', compact('item'));
    }

    public function edit($id)
    {
        $stockConsumption = StockConsumption::with(['item', 'store', 'uom', 'creator'])->findOrFail($id);
        //$this->authorize('update', $stockConsumption);

        $branches = Branch::all();
        $users = User::all();
        $stores = Store::all();
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);

        return view('inventory.stockmanagement.stockconsumption.edit', compact('branches', 'items', 'uoms', 'types', 'stockConsumption','users'));
    }

    public function update(StockConsumptionRequest $request, $id)
    {
        $stockConsumption = StockConsumption::findOrFail($id);
        //$this->authorize('update', $stockConsumption);

        try {
            $this->stockConsumptionService->update($stockConsumption, $request->validated());
            return redirect()->route('stockconsumption.index')->with('success', 'Stock consumption updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update stock consumption: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $stockConsumption = StockConsumption::findOrFail($id);
        //$this->authorize('delete', $stockConsumption);

        try {
            $this->stockConsumptionService->delete($stockConsumption);
            return redirect()->route('stockconsumption.index')->with('success', 'Stock consumption deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete stock consumption: ' . $e->getMessage());
        }
    }

    public function getUOM(Request $request)
    {
        $item = ItemMasterList::with('uom')->find($request->get('ItemID'));
        return response()->json(['uom' => $item?->uom]);
    }


    public function getStores(Request $request)
    {
        $branchId = $request->get('BranchID');
        if (!$branchId) {
            return response()->json([], 400);
        }
        $stores = Store::where('BranchID', $branchId)->get(['Id', 'StoreName']);
        return response()->json($stores);
    }


  public function getIssuedToOptions(Request $request)
{
    $codeDetail = \App\Models\Core\CodeDetail::find($request->get('type'));
    $type = strtoupper($codeDetail?->Description ?? '');

    switch ($type) {
        case 'EMPLOYEE':
            return Employee::select('Id', DB::raw("CONCAT(FirstName, ' ', LastName) AS Name"))->get();

        case 'DEPARTMENT':
            return Department::select('Id', 'Name')->get();

        default:
            return response()->json([], 200);
    }

}


}
