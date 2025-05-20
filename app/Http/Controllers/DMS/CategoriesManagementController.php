<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class CategoriesManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.categoriesmanagement.index');
    }

    public function create(){
        return view('dms.categoriesmanagement.create');
    }
}
