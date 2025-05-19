<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

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
