<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseTrackerController extends Controller
{
         public function create()
    {
        return view("legal.notices.responsetracker.create");
    }

    public function index()
    {
        return view("legal.notices.responsetracker.index");
    }
}
