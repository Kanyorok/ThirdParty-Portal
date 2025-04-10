<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class HelpController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $help = $request->get('help');
        $help = $help ?? '';
        if(!View::exists('help.'.$help)){
            return $this->errored('help page not found');
        }

        return view('help.'.$help);
    }
}
