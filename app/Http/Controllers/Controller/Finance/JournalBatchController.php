<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JournalBatchController extends Controller
{
    public function index()
    {
        return view('finance.journalbatch.index');
    }

    public function create()
    {
        return view('finance.journalbatch.create');
    }
}
