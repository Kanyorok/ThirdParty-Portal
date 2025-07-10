<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\PrequalificationPeriodEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\PrequalificationPeriodRequest;
use App\Models\Procurement\PrequalificationPeriod;
use App\Services\Procurement\Suppliers\PrequalicicationPeriodService;
use Carbon\Carbon;
use DateTime;

class PrequalificationPeriodController extends Controller
{
    protected $service;

    public function __construct(PrequalicicationPeriodService $service)
    {
        $this->service = $service;
    }

    //
    public function index()
    {
        $periods = PrequalificationPeriod::all();
        return view('procurement.suppliers.prequalification.prequalifiedperiods.index', compact('periods'));
    }


    public function create()
    {
        return view('procurement.suppliers.prequalification.prequalifiedperiods.create');
    }

    public function store(PrequalificationPeriodRequest $request)
    {
        $this->authorize('store', PrequalificationPeriod::class);
        $status = PrequalificationPeriodEnum::from($request->input('Status'));
        $prequalificationPeriod = PrequalicicationPeriodService::create(
            $request->Title,
            $request->Description,
            Carbon::createFromFormat('d/m/Y', $request->input('StartDate')),
            Carbon::createFromFormat('d/m/Y', $request->input('EndDate')),
            $request->MaxVendors,
            $status,
            auth()->user()
        );
        return redirect()->route('preqrounds.store')
            ->with('success', 'Prequalification Period Created Successfully');
    }

    public function show($Id)
    {
        $this->authorize('view', PrequalificationPeriod::class);
        $period = PrequalificationPeriod::findOrFail($Id);
        return view('procurement.suppliers.prequalification.prequalifiedperiods.show', compact('period'));
    }

    public function edit($Id)
    {
        $this->authorize('update', PrequalificationPeriod::class);
        $period = PrequalificationPeriod::findOrFail($Id);
        return view('procurement.suppliers.prequalification.prequalifiedperiods.edit', compact('period'));
    }

    public function update(PrequalificationPeriodRequest $request, $Id)
    {
        $status = PrequalificationPeriodEnum::from($request->input('Status'));

        $periodId = PrequalificationPeriod::findOrFail($Id);

        $this->authorize('update', $periodId);
        $this->service->update(
            $periodId,
            $request->Title,
            $request->Description,
            Carbon::createFromFormat('d/m/Y', $request->input('StartDate')),
            Carbon::createFromFormat('d/m/Y', $request->input('EndDate')),
            $request->MaxVendors,
            $status,
            $request->user()
        );

        return redirect()->route('preqrounds.index')
            ->with('success', 'Prequalification Period Updated Successfully');
    }

    public function destroy($Id)
    {
        $periodId = PrequalificationPeriod::findOrFail($Id);
        $this->authorize('destroy', $periodId);

        $this->service->delete($periodId);

        return redirect()->route('itemtype.index')->with('success', 'Prequalification Period Deleted Successfully.');
    }


}
