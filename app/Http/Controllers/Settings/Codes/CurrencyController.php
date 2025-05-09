<?php

namespace App\Http\Controllers\Settings\Codes;

use App\Http\Controllers\Controller;
use App\Models\CodeDetail;
use App\Models\Core\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():JsonResponse
    {
        $this->authorize('view', CodeDetail::class);

        try {
            return Datatables::of(Currency::query()->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
                ->addColumn('action', function (Currency $currency) {
                    return '--';
                })->rawColumns(['action'])->make();
        } catch (\Exception $e) {}

        return $this->errored('unexpected error, try again later');
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currency $currency)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        //
    }
}
