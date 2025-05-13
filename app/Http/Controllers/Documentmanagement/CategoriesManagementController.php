<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoriesManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.categoriesmanagement.index');
    }

    public function create(){
        return view('documentmanagement.categoriesmanagement.create');
    }
}
