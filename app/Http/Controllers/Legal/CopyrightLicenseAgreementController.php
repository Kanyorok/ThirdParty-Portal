<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CopyrightLicenseAgreementController extends Controller
{
    public function create()
    {
        return view("legal.intellectualproperty.copyrightlicenseagreement.create");
    }

    public function index()
    {
        return view("legal.intellectualproperty.copyrightlicenseagreement.index");
    }
}
