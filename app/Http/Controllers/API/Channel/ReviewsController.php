<?php

namespace App\Http\Controllers\API\Channel;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Services\BR\ClientService;
use App\Services\Feedback\ReviewService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReviewsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                                        'ratting'     => [
                                                          'required_without:review',
                                                          'integer',
                                                          'between:1,5',
                                                         ],
                                        'review'      => [
                                                          'required_without:ratting',
                                                          'string',
                                                          'max:5000',
                                                         ],
                                        'name'        => [
                                                          'nullable',
                                                          'string',
                                                          'max:200',
                                                         ],
                                        'clientID'    => [
                                                          'nullable',
                                                          'string',
                                                         ],
                                        'phoneNumber' => [
                                                          'nullable',
                                                          'string',
                                                         ],
                                       ]);
        } catch (ValidationException $e) {
            return $this->br_response(422, $e->getMessage(), $e->errors());
        }

        $clientID = $request->get('clientID');
        $phoneNo = $request->get('phoneNumber');
        if (!is_string($clientID) && !is_string($phoneNo)) {
            return $this->br_response(422, 'clientID or phoneNumber  is required', [
                                                                                    'clientID'    => 'clientID is required when phoneNumber is not provided',
                                                                                    'phoneNumber' => 'phoneNumber is required when clientID is not provided',
                                                                                   ]);
        }

        $client = Client::query()->where('ClientID', $clientID)->orWhere(function (Builder $query) use ($phoneNo) {
            return ClientService::search($query, $phoneNo);
        })->first();
        if (!$client instanceof Client) {
            return $this->br_response(422, 'member id is not found', [
                                                                      'clientID'    => 'member id is not found',
                                                                      'phoneNumber' => 'phoneNumber is not found',
                                                                     ]);
        }

        $actor = SystemHelper::user();
        try {
            DB::transaction(static function () use ($client, $data, $actor) {
                ReviewService::client($client->ClientID, $data['ratting'] ?? 3, $data['review'] ?? '', 'Channels', $actor);
            });
        } catch (Exception $e) {
            Log::error('Error saving review from channel : ' . $e->getMessage());
            return $this->br_response(400, 'unexpected error, try again later');
        }

        return $this->br_response('000', 'thank you for your feedback');
    }
}
