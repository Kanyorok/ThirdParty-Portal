<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TradeMarkRegisterController extends Controller
{
    public function create()
    {
        return view("legal.intellectualproperty.trademarkregister.create");
    }

    public function index()
    {
        return view("legal.intellectualproperty.trademarkregister.index");
    }
}
