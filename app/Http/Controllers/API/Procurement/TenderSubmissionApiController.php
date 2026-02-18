<?php

namespace App\Http\Controllers\API\Procurement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\BidSubmissions\ListBidSubmissionsRequest;
use App\Http\Requests\Procurement\BidSubmissions\StoreBidSubmissionRequest;
use Illuminate\Http\JsonResponse;

class TenderSubmissionApiController extends Controller
{
    public function index(ListBidSubmissionsRequest $request): JsonResponse
    {
        return app(BidSubmissionApiController::class)->getSupplierBids($request);
    }

    public function store(StoreBidSubmissionRequest $request): JsonResponse
    {
        return app(BidSubmissionApiController::class)->store($request);
    }
}
