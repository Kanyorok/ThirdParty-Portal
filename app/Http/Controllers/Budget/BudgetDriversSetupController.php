<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriver;
use App\Models\Budget\BudgetLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetDriversSetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Check permissions
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetDriver::class);

        $drivers = BudgetDriver::all();

        return view('budgetandanalytics.settings.budgetdrivers', compact('drivers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Check Perm
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetLine::class);

        $validated = $request->validate([
            'DriverName' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $driver = BudgetDriver::create([
                'DriverName' => $validated['DriverName'],
                'IsActive' => $request->has('IsActive') ? 1 : 0,

                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id()

            ]);

            DB::commit();
            //LOG Activity
            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Create a Budget Driver');

            return back()->with('success', 'Budget driver created successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Failed to store budget line mapping.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An Error Occurred. Please try again');
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Check Permissions
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetLine::class);

        $validated = $request->validate([
            'DriverName' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $driver = BudgetDriver::where('Id', $id)->update([
                'DriverName' => $validated['DriverName'],
                'IsActive' => $request->has('IsActive') ? 1 : 0,
            ]);

            DB::commit();
            //LOG Activity
            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated a Budget Driver');

            return back()->with('success', 'Budget driver Updated successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to store budget driver.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An Error Occurred. Please try again');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // check Perm
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetLine::class);

        try {
            $driver = BudgetDriver::find($id);
            $dr = $driver;
            $driver->delete();
            //LOG Activity
            activity()
                ->performedOn($dr)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted a Budget Driver');
            return back()->with('success', 'Driver deleted Successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete budget line mapping.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An Error Occurred. Please try again');
        }
    }
}
