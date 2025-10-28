<?php

namespace App\Http\Controllers\DMS\Settings;

use App\Http\Controllers\Controller;
use App\Models\DMS\DMSSignature;
use App\Traits\Controller\DocumentsTrait;
use Illuminate\Http\Request;

class SignatureDocumentsController extends Controller
{
    use DocumentsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, DMSSignature $dMSSignature)
    {
        $this->authorize('view', $dMSSignature);
        return $this->documents($dMSSignature->documents(), $request->user());
    }
}
