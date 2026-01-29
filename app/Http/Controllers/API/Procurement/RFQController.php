<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Resources\Procurement\RFQCollection;
use App\Models\Procurement\RFQ;

class RFQController extends Controller
{
    public function index(): RFQCollection
    {
        $rfq = RFQ::with([
         'id',
         'rfq_number',
         'comments',
         'status',
         'created_at',
         'updated_at',
        ])
        ->paginate(10);

        return   new RFQCollection($rfq);
    }
}
