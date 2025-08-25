<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Enums\Procurement\PrequalificationApplicationEnum;
use Illuminate\View\View;

class PrequalifiedSuppliersController extends Controller
{
    public function index(): View
    {
        $prequalifiedApplications = PrequalificationApplication::where(
            'Status',
            PrequalificationApplicationEnum::Approved
        )->with('supplier')->get();

        return view('procurement.suppliers.prequalification.prequalifiedsuppliers.index', [
            'prequalifiedApplications' => $prequalifiedApplications,
        ]);
    }
}
