<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;
use App\Models\FleetManagement\FleetMake; 
use App\Http\Requests\FleetManagement\FleetMakeRequest;
use App\Http\Requests\FleetManagement\FleetModelRequest;
use App\Policies\FleetManagement\FleetMakePolicy;

use App\Services\FleetManagement\FleetMakeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class FleetMakeController extends Controller
{
    protected FleetMakeService $service;

    public function __construct(FleetMakeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetMake::class);
        $fleetMakes = FleetMake::withCount('vehicles')->get();
        return view('fleetmanagement.fleetmake.index', compact('fleetMakes'));
    }  

    public function create()
    {
        $this->authorize('create', FleetMake::class);
        return view('fleetmanagement.fleetmake.create');
    }
  
   
 public function store(FleetMakeRequest $request)
{
    $this->authorize('create', FleetMake::class);
    $validated = $request->validated();

    try {
        $fleetMake = $this->service->create($validated);

        return redirect()
            ->route("fleetmake.index")
            ->with('success', $fleetMake->wasRecentlyCreated
                ? 'Fleet Make/Brand created successfully.'
                : 'Fleet Make/Brand restored successfully.');
    } catch (\Exception $e) {
        return back()
            ->withErrors(['BrandName' => $e->getMessage()])
            ->withInput();
    }
}


    public function show($id)
        {
            $this->authorize('view', FleetMake::class);
            $fleetMake = FleetMake::findOrFail($id);
            return view('fleetmanagement.fleetmake.show', compact('fleetMake'));
        }

    public function update(FleetMakeRequest $request, $id)
        {
            $this->authorize('update', FleetMake::class);
            $fleetMake = FleetMake::findOrFail($id);
            $fleetMake->update($request->validated());

            return redirect()
                ->route('fleetmake.index')
                ->with('success', 'Fleet Make/Brand updated successfully.');
        }

        public function edit($id)
        {
            $this->authorize('edit', FleetMake::class);
            $fleetMake = FleetMake::findOrFail($id);
            return view('fleetmanagement.fleetmake.edit', compact('fleetMake'));
        } 

       public function destroy($id)
        {
            $this->authorize('destroy', FleetMake::class);
            $fleetMake = FleetMake::findOrFail($id);
            $this->service->delete($fleetMake); 

            return redirect()
                ->route("fleetmake.index")
                ->with('success', 'Fleet Make/Brand deleted successfully.');
        }

    }
