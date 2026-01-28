<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MessageRequest;
use App\Models\BR\Client;
use App\Services\SMSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClientMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Client $client): JsonResponse
    {
        return SMSService::dt($client->crmsms(), ['source']);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MessageRequest $request, Client $client): JsonResponse
    {

        $phone = $request->getClientPhone($client);

        try {
            $activity = DB::transaction(static function () use ($phone, $client, $request) {
                $service = SMSService::createClient($client, $request->validated('message_content'), $request->user(), $phone);
                $activity = $service->addActivity(now());
                $service->send();

                return $activity;
            });
        } catch (Exception $e) {
            Log::error('Error sending sms to client ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message', data: ['activity' => $activity]);
    }
}
