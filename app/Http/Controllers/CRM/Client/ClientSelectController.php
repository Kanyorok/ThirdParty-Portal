<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\Select2Request;
use App\Models\BR\Client;
use Illuminate\Http\JsonResponse;

class ClientSelectController extends Controller
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
        if (is_string($search) && ! empty($search)) {
            $data = Client::query()
                ->where(function ($query) use ($search) {
                    $query->where('Name', 'LIKE', "%$search%")
                        ->orWhere('ClientID', 'LIKE', "%$search%");
                })->lock('WITH(NOLOCK)')->select(['ClientID', "Name"])->limit(10)->get(['ClientID', "Name"]);
        }

        return response()->json($data);
    }
}
