<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;

class LegalDispatchController extends Controller
{
    public function index()
    {
        return view('legal.dispatch.index');
    }
}

