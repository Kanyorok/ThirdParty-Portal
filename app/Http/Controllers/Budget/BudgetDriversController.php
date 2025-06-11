<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriver;
use App\Models\Budget\BudgetDriverMaster;
use App\Models\Budget\BudgetLine;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetDriversController extends Controller
{
    //
    public function index()
    {
        //check Perm
        $this->authorize(PermissionEnum::BudgetSetupView,BudgetLine::class);

        $drivers=BudgetDriverMaster::with('driverType')->get();
        $driverTypes=BudgetDriver::where('IsActive',1)->get();
        //$uom=UnitOfMeasure::all();
        return view('budgetandanalytics.budgetdrivers.index',compact(
            'drivers',
            'driverTypes',
            //'uom'
        ));
    }

    public function create()
    {
        return view('budgetandanalytics.budgetdrivers.create');
    }

    public function store(Request $request){
        //Check Permission
        $this->authorize(PermissionEnum::BudgetSetupCreate,BudgetLine::class);

        //Validate Incoming request
        $validated=$request->validate([
            'DriverName'=>'required|string',
            'DriverType'=>'required|integer',
            //'UOM'=>'required|integer',
            'Frequency'=>'required|string'

        ]);

        try {
            DB::beginTransaction();
            $driver=BudgetDriverMaster::create([
                'DriverName'=>$validated['DriverName'],
                'DriverTypeID'=>$validated['DriverType'],
                //'UOMID'=>$validated['UOM'],
                'Frequency'=>$validated['Frequency'],
                'IsActive'   => $request->has('IsActive') ? 1 : 0,

                'CreatedBy' => Auth::id(),
                'ModifiedBy'=>Auth::id()
            ]);

            DB::commit();
            //LOG Activity
            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created a Budget Driver');
            return back()->with('success','Budget driver Created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to store budget driver.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error','An Error Occurred. Please try again');
        }
    }
}
