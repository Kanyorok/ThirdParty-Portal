<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExternalDirectoryController extends Controller
{
            public function create()
    {
        return view("legal.consultantmanagement.externaldirectory.create");
    }

    public function index()
    {
        return view("legal.consultantmanagement.externaldirectory.index");
    }
}
