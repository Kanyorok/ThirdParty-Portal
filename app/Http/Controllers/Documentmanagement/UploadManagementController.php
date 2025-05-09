<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.uploadmanagement.index');
    }

    public function create(){
        return view('documentmanagement.uploadmanagement.create');
    }
}
