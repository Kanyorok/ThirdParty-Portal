<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PeriodManagementController extends Controller
{
    public function index()
    {
        return view('finance.periodmanagement.index');
    }
    public function create()
    {
        return view('finance.periodmanagement.create');
    }
}
