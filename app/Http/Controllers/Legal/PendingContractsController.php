<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PendingContractsController extends Controller
{
        public function create()
    {
        return view("legal.reportsmanagement.pendingcontracts.create");
    }

    public function index()
    {
        return view("legal.reportsmanagement.pendingcontracts.index");
    }
}
