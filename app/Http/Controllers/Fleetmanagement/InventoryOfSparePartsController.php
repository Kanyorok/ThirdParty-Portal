<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryOfSparePartsController extends Controller
{
    public function create()
    {
        return view("fleetmanagement.inventoryofspareparts.create");
    }

    public function index()
    {
        return view("fleetmanagement.inventoryofspareparts.index");
    }
}
