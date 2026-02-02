<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockConsumptionRequest;
use App\Models\Inventory\StockConsumption;
use App\Policies\Inventory\StockConsumptionPolicy;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use App\Models\Inventory\StockItem;
use App\Models\Auth\User;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\HRM\Employee;
use App\Models\HRM\Department;
use App\Models\Core\Approval\CodeDetail;
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

    public function index(Request $request)
    {
        $this->authorize('viewAny', StockConsumption::class);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $consumptions = StockConsumption::with(['item', 'store', 'uom', 'issuedBy', 'branch', 'stockItem'])
            ->where('BranchID', $branchId)
            ->get();

        return view('inventory.stockmanagement.stockconsumption.index', compact('consumptions'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', StockConsumption::class);
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branch = Branch::findOrFail($branchId);

        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;
        $issuedByDisplay = $currentEmployee
            ? $currentEmployee->FirstName . ' ' . $currentEmployee->LastName . ' (' . ($currentEmployee->EmployeeID ?? $currentUser->UserName) . ')'
            : $currentUser->UserName;

        $employees = Employee::with('user')
            ->where('BranchId', $branchId)
            ->whereHas('user', function ($q) use ($currentUser) {
                $q->where('Id', '!=', $currentUser->Id);
            })
            ->select('Id', 'FirstName', 'LastName', 'EmployeeID')
            ->orderBy('FirstName')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->user ? $employee->user->Id : null,
                    'name' => $employee->FirstName . ' ' . $employee->LastName .
                        ($employee->EmployeeID ? ' (' . $employee->EmployeeID . ')' : '')
                ];
            })
            ->filter(function ($item) {
                return !is_null($item['id']);
            });

        $departments = Department::select('Id', 'Name')
            ->orderBy('Name')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->Id,
                    'name' => $department->Name
                ];
            });

        $stores = Store::where('BranchID', $branchId)->get();
        $items = StockItem::where('Branch', $branchId)->get();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);

        return view('inventory.stockmanagement.stockconsumption.create', compact(
            'branch',
            'stores',
            'items',
            'uoms',
            'types',
            'employees',
            'departments',
            'issuedByDisplay',
            'currentUser'
        ));
    }

    public function store(StockConsumptionRequest $request)
    {
        $this->authorize('create', StockConsumption::class);
        try {
            $this->stockConsumptionService->create($request->validated());
            return redirect()->route('stockconsumption.index')->with('success', 'Stock consumption recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to record stock consumption: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id, Request $request)
    {
        $this->authorize('update', StockConsumption::class);
        $consumption = StockConsumption::with(['item', 'store', 'uom', 'issuedBy', 'branch', 'stockItem'])->findOrFail($id);

        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $branch = Branch::findOrFail($branchId);

        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;
        $issuedByDisplay = $currentEmployee
            ? $currentEmployee->FirstName . ' ' . $currentEmployee->LastName . ' (' . ($currentEmployee->EmployeeID ?? $currentUser->UserName) . ')'
            : $currentUser->UserName;

      
        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })
            ->with('employee')
            ->get()
            ->map(function ($user) {
                $employee = $user->employee;
                return [
                    'Id' => $user->Id,
                    'Name' => $employee
                        ? $employee->FirstName . ' ' . $employee->LastName .
                        ($employee->EmployeeID ? ' (' . $employee->EmployeeID . ')' : '')
                        : $user->UserName
                ];
            });

        $employees = Employee::with('user')
            ->where('BranchId', $branchId)
            ->whereHas('user', function ($q) use ($currentUser) {
                $q->where('Id', '!=', $currentUser->Id);
            })
            ->select('Id', 'FirstName', 'LastName', 'EmployeeID')
            ->orderBy('FirstName')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->user ? $employee->user->Id : null,
                    'name' => $employee->FirstName . ' ' . $employee->LastName .
                        ($employee->EmployeeID ? ' (' . $employee->EmployeeID . ')' : '')
                ];
            })
            ->filter(function ($item) {
                return !is_null($item['id']);
            });

        $departments = Department::select('Id', 'Name')
            ->orderBy('Name')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->Id,
                    'name' => $department->Name
                ];
            });

        $stores = Store::where('BranchID', $branchId)->get();
        $items = StockItem::where('Branch', $branchId)->get();
        $uoms = UnitOfMeasure::all();
        $types = CodeDetail::where('CodeID', 'IssuedToType')->get(['ID', 'Description']);

        $preSelectedValue = '';
        if ($consumption->IssuedToType) {
            $type = CodeDetail::find($consumption->IssuedToType);
            if ($type && strtoupper($type->Description) === 'EMPLOYEE') {
                $preSelectedValue = $consumption->IssuedToID;
            } elseif ($type && strtoupper($type->Description) === 'DEPARTMENT') {
                $preSelectedValue = $consumption->IssuedToID;
            }
        }

        return view('inventory.stockmanagement.stockconsumption.edit', compact(
            'consumption',
            'branch',
            'stores',
            'items',
            'uoms',
            'types',
            'users',
            'employees',
            'departments',
            'issuedByDisplay',
            'currentUser',
            'preSelectedValue'
        ));
    }

    public function update(StockConsumptionRequest $request, $id)
    {
        $this->authorize('update', StockConsumption::class);

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
        $this->authorize('view', StockConsumption::class);

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
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return response()->json([], 400);
        }

        $branchId = $currentBranch->Id;
        $stores = Store::where('BranchID', $branchId)->get(['Id', 'StoreName']);
        return response()->json($stores);
    }

    public function getItems(Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return response()->json([], 400);
        }

        $branchId = $currentBranch->Id;
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
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return response()->json([], 400);
        }

        $codeDetail = CodeDetail::find($request->get('type'));
        $type = strtoupper($codeDetail?->Description ?? '');

        switch ($type) {
            case 'EMPLOYEE':
                $employees = Employee::with('user')
                    ->where('BranchId', $currentBranch->Id)
                    ->whereHas('user', function ($q) {
                        $q->where('Id', '!=', Auth::id());
                    })
                    ->select('Id', 'FirstName', 'LastName', 'EmployeeID')
                    ->orderBy('FirstName')
                    ->get()
                    ->map(function ($employee) {
                        return [
                            'Id' => $employee->user ? $employee->user->Id : $employee->Id,
                            'Name' => $employee->FirstName . ' ' . $employee->LastName .
                                ($employee->EmployeeID ? ' (' . $employee->EmployeeID . ')' : '')
                        ];
                    });

                return response()->json($employees);

            case 'DEPARTMENT':
                $departments = Department::select('Id', 'Name')
                    ->orderBy('Name')
                    ->get()
                    ->map(function ($department) {
                        return [
                            'Id' => $department->Id,
                            'Name' => $department->Name
                        ];
                    });

                return response()->json($departments);

            default:
                return response()->json([], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete', StockConsumption::class);
        $item = StockConsumption::findOrFail($id);

        try {
            $this->stockConsumptionService->delete($item);
            return redirect()->route('stockconsumption.index')->with('success', '🗑️ Stock consumption deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete stock consumption: ' . $e->getMessage());
        }
    }
}
