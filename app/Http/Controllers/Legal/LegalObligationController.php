<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;

class LegalObligationController extends Controller
{
    public function index()
    {
        return view('legal.obligations.index');
    }
}
