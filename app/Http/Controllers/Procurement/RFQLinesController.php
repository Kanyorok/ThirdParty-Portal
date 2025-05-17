<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RFQLinesController extends Controller
{
    public function create()
    {
        // Logic to create RFQ lines
        return view('procurement.rfqlines.create');
    }
}
