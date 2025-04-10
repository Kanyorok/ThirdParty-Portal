<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\Select2Request;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;

class LeadSelectController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Select2Request $request): JsonResponse
    {
        $data = [];
        $search = $request->getSearchString();
        if (is_string($search) && !empty($search)) {
            $leads = Lead::query()
                ->where(function ($query) use ($search) {
                    $query->where('Name', 'LIKE', "%$search%")
                        ->orWhere('OtherNames', 'LIKE', "%$search%")
                        ->orWhere('Email', 'LIKE', "%$search%")
                        ->orWhere('LeadID', 'LIKE', "%$search%");
                })->lock('WITH(NOLOCK)')->select(['LeadID', "Name", "OtherNames"])->limit(10)->get(['LeadID', "Name", "OtherNames"]);
            $data = $leads->map(function ($lead) {
                return [
                    'LeadID' => $lead->LeadID,
                    'Name' => $lead->Name . ' ' . $lead->OtherNames,
                ];
            });
        }

        return response()->json($data);
    }
}
