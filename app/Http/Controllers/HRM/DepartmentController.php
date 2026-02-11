<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\HRM\DepartmentRequest;
use App\Models\HRM\Department;
use App\Services\HRM\DepartmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except('index');
        $this->authorizeResource(Department::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            try {
                return Datatables::of(Department::with(['head'])->withCount('employees')->select('*'))->addIndexColumn()
                    ->addColumn('action', function (Department $department) {
                        return '<button type="button" data-click_url="' . route('departments.show', [$department->DepartmentID]) . '" data-summary_title="department details" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
                    })->addColumn('employees_count', function (Department $department) {
                        return number_format($department->employees_count);
                    })->addColumn('hod', function (Department $department) {
                        return $department->head ? $department->head->Name : '-';
                    })->editColumn('DepartmentID', function (Department $department) {
                        return Str::upper($department->DepartmentID);
                    })->rawColumns(['action',])->make();
            } catch (Exception $e) {
            }

            return $this->errored('cannot retrieve department list.');
        }

        return view('hrms.department.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $dpt = DepartmentService::create(
                    name: $request->string('Name')->trim()->toString(),
                    actor: $request->user(),
                    description: $request->string('Description')->trim()->toString()
                )->department;

                return $this->succeeded($dpt->DepartmentID . ' created successfully.');
            });
        } catch (Throwable | Exception $e) {
            Log::error("--- CREATE DEPARTMENT ERROR --- " . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('create department failed.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('hrms.department.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return view('hrms.department.show', ['department' => $department]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        try {
            return DB::transaction(function () use ($request, $department) {
                $department->update([
                    'Name' => $request->string('Name')->trim()->toString(),
                    'Description' => $request->string('Description')->trim()->toString(),
                    'ModifiedBy' => $request->user()->Id,
                ]);

                activity()->causedBy($request->user())->performedOn($department)->event('update')->log('updated department ' . $department->DepartmentID);

                return $this->succeeded($department->DepartmentID . ' updated successfully.');
            });
        } catch (Throwable | Exception $e) {
            Log::error("--- UPDATE   DEPARTMENT ERROR --- " . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('update department failed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Department $department): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $department) {
                $department->forceFill([
                    'DeletedBy' => $request->user()->Id,
                    'DeletedOn' => now(),
                ])->save();

                activity()->causedBy($request->user())->performedOn($department)->event('delete')->log('deleted department ' . $department->DepartmentID);

                return $this->succeeded($department->DepartmentID . ' deleted successfully.');
            });
        } catch (Throwable | Exception $e) {
            Log::error("--- DELETE   DEPARTMENT ERROR --- " . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('update department failed.');
    }
}
