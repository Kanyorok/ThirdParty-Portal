<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class ReconUploadController extends Controller
{
    public function index()
    {
        return view('finance.bankreconciliation.excelupload.index');
    }

    public function create()
    {
        return view('finance.bankreconciliation.excelupload.create');
    }
}
