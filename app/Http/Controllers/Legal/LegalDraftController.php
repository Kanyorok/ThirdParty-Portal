<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;

class LegalDraftController extends Controller
{
    public function index()
    {
        return view('legal.drafts.index');
    }
}
