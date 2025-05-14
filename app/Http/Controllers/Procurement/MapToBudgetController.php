<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MapToBudgetController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.index');
    }

    public function create(){
        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.create');
    }

}
