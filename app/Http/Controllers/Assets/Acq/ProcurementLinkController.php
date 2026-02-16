<?php

namespace App\Http\Controllers\Assets\Acq;

use App\Http\Controllers\Controller;
use App\Models\Assets\Acq\ProcurementDoc;
use Illuminate\Http\Request;

class ProcurementLinkController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $typ = $request->get('type'); // PO/GRN/All
        $rows = ProcurementDoc::when($q, fn ($qq) => $qq->where('DocNo', 'like', "%$q%")
                                                   ->orWhere('SupplierName', 'like', "%$q%"))
                    ->when($typ, fn ($qq) => $qq->where('DocType', $typ))
                    ->orderByDesc('DocDate')->paginate(20);

        return view('assets.acq.procurement.index', compact('rows', 'q', 'typ'));
    }
}
