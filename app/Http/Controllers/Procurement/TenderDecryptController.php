<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class TenderDecryptController extends Controller
{
    public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.decryptbid.index');
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.decryptbid.create');
    }
}
