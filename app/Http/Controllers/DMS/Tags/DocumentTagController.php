<?php

namespace App\Http\Controllers\DMS\Tags;

use App\Http\Controllers\Controller;
use App\Models\DMS\DMSTags;
use App\Traits\Controller\DocumentsTrait;
use Illuminate\Http\Request;

class DocumentTagController extends Controller
{
    use DocumentsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, DMSTags $dMSTags)
    {
        $this->authorize('view', $dMSTags);
        return $this->documents($dMSTags->documents(), $request->user());
    }
}
