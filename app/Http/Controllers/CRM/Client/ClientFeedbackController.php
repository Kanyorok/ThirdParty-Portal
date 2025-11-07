<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Review;
use App\Traits\Controller\ReviewsTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class ClientFeedbackController extends Controller
{
    use ReviewsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     * @throws Exception
     */
    public function __invoke(Client $client): JsonResponse
    {
        $this->authorize('viewAny', Review::class);
        return $this->reviews($client->reviews());
    }
}
