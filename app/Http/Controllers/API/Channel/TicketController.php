<?php

namespace App\Http\Controllers\API\Channel;

use App\Enums\TicketPriorityEnum;
use App\Enums\TicketSourceEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketCollection;
use App\Http\Resources\TicketResource;
use App\Models\BR\Client;
use App\Models\Core\CodeDetail;
use App\Services\StaticListsService;
use App\Traits\Controller\TicketsTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    use TicketsTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(string $ClientID): TicketCollection|JsonResponse
    {
        $client = Client::query()->where('ClientID', $ClientID)->first();
        if (!$client instanceof Client) {
            return $this->errored('client may be invalid');
        }

        return $this->br_response(
            '000',
            'success',
            ['data' => new TicketCollection($client->tickets()->paginate(20))]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $ClientID): JsonResponse
    {
        $client = Client::query()->where('ClientID', $ClientID)->first();
        if (!$client instanceof Client) {
            return $this->br_response(422, 'member id may be invalid');
        }

        try {
            $data = $request->validate([
                'ticket_id' => [
                    'required',
                    'string',
                    'max:100',
                ],
                'ticket_title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'ticket_description' => [
                    'required',
                    'string',
                ],
                'ticket_category' => ['required'],
            ]);
        } catch (ValidationException $e) {
            return $this->br_response(422, $e->getMessage(), $e->errors());
        }

        $category = StaticListsService::getRawList(StaticListsService::TicketCategories)->where('ID', $data['ticket_category'])->first();
        if (!$category instanceof CodeDetail) {
            return $this->br_response(422, 'category may be invalid', ['category' => 'category may be invalid']);
        }

        $actor = SystemHelper::user();
        try {
            $service = $this->save($client, $category, $data['ticket_title'], $data['ticket_description'], $actor, TicketSourceEnum::Channels, TicketPriorityEnum::Normal, SourceTicketID: $data['ticket_id']);
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error channels creating Client ticket ' . $e->getMessage());
            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->br_response(
            '000',
            'ticket created',
            ['data' => new TicketResource($service->ticket)]
        );
    }
}
