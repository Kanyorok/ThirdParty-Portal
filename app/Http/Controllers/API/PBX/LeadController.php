<?php

namespace App\Http\Controllers\API\PBX;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        Log::warning('Lead Create');
        Log::info(json_encode($request->all()));

        return $this->succeeded('ok');
    }
}
