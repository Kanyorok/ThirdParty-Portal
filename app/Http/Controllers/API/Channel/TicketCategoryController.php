<?php

namespace App\Http\Controllers\API\Channel;

use App\Http\Controllers\Controller;
use App\Http\Resources\CodeDetailCollection;
use App\Services\StaticListsService;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): CodeDetailCollection
    {
        return new CodeDetailCollection(StaticListsService::getRawList(StaticListsService::TicketCategories)->paginate('15'));
    }
}
