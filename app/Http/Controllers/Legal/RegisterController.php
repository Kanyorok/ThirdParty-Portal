<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
         public function create()
    {
        return view("legal.notices.register.create");
    }

    public function index()
    {
        return view("legal.notices.register.index");
    }
}
