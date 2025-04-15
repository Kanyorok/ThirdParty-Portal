<?php

namespace App\Http\Controllers\CRM\DebtCollection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\MessageRequest;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Services\BR\LoanService;
use App\Services\SMSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoanMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            throw new Exception('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        return SMSService::dt($product->crmsms(), ['party']);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(MessageRequest $request, string $product_id): JsonResponse
    {
        $product = DebtProduct::query()->where('AccountID', $product_id)->oldest('processDate')->first();
        if (!$product instanceof DebtProduct) {
            return $this->errored('Product not found, maybe closed.');
        }
        $this->authorize('view', $product);

        $client = $product->client;
        if (!$client instanceof Client) {
            throw ValidationException::withMessages(['message_to' => 'phone number maybe invalid']);
        }
        $request->getClientPhone($client);

        try {
            $activity = DB::transaction(static function () use ($product, $request) {
                return (new LoanService($product))->message($request->validated('message_content'), $request->user());
            });
        } catch (Exception $e) {
            Log::error('Error sending sms to client ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('sending message', data: ['activity' => $activity]);
    }
}
