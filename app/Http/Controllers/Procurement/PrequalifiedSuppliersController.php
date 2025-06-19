<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrequalifiedSuppliersController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.prequalifiedsuppliers.index');
    }

    public function create()
    {
        return view('procurement.suppliers.prequalification.prequalifiedsuppliers.create');
    }

    public function show()
    {
        return view('procurement.suppliers.prequalification.prequalifiedsuppliers.show');
    }
}
