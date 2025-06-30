<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Suppliers\PrequalificationPeriodRequest;
use App\Models\Procurement\PrequalificationPeriod;
use App\Services\Procurement\Suppliers\PrequalicicationPeriodService;
use Illuminate\Http\Request;

class PrequalificationPeriodController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.prequalifiedperiods.index');
    }

    public function create()
    {
        return view('procurement.suppliers.prequalification.prequalifiedperiods.create');
    }

    Public function store(PrequalificationPeriodRequest $request)
    {
        $this->authorize('store', PrequalificationPeriod::class);
        $prequalificationPeriod = PrequalicicationPeriodService::create(
            $request->Title,
            $request->Description,
            $request->StartDate,
            $request->EndDate,
            auth()->user()
        );
        return redirect()->route('preqrounds.store')
            ->with('success', 'Prequalification Period Created Successfully');
    }

}
