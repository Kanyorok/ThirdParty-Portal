<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use App\Traits\Controller\DiscussionTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class LeadDiscussionController extends Controller
{
    use DiscussionTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function __invoke(Lead $lead): JsonResponse
    {
        return $this->discussions($lead->discussions());
    }
}
