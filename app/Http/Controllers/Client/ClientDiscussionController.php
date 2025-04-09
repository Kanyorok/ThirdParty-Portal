<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Traits\Controller\DiscussionTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class ClientDiscussionController extends Controller
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
    public function __invoke(Client $client): JsonResponse
    {
        return $this->discussions($client->discussions());
    }
}
