<?php

namespace App\Http\Controllers\API\Channel;

use App\Http\Controllers\Controller;
use App\Http\Resources\CodeDetailCollection;
use App\Models\Locality;
use App\Services\StaticListsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $type = $request->get('Type');
        if ($type === 'Location') {//City
            return $this->br_response('000',
                'success', ['data' => new CodeDetailCollection(Locality::query()->paginate('15'))]);
        }
        if ($type === 'Industry') {
            return $this->br_response('000',
                'success', ['data' => new CodeDetailCollection(StaticListsService::getRawList(StaticListsService::Industries)->paginate('15'))]);
        }

        if ($type === 'TicketCategory') {
            return $this->br_response('000',
                'success', ['data' => new CodeDetailCollection(StaticListsService::getRawList(StaticListsService::TicketCategories)->paginate('15'))]);
        }

        if ($type === 'CustomerType') {
            return $this->br_response('000',
                'success', ['data' => new CodeDetailCollection(StaticListsService::getRawList(StaticListsService::CustomerType)->paginate('15'))]);
        }

        if ($type === 'MarketingMode') {
            return $this->br_response('000',
                'success', ['data' => new CodeDetailCollection(StaticListsService::getRawList(StaticListsService::MarketingModes)->paginate('15'))]);
        }

        return $this->br_response(400, 'error');
    }
}
