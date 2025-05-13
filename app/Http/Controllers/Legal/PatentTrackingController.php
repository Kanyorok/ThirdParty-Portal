<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatentTrackingController extends Controller
{
    public function create()
    {
        return view("legal.intellectualproperty.patenttracking.create");
    }

    public function index()
    {
        return view("legal.intellectualproperty.patenttracking.index");
    }
}
