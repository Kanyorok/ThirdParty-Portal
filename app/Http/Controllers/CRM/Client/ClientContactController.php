<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ContactRequest;
use App\Models\BR\Client;
use App\Models\Communication\EmailConversation;
use App\Traits\Controller\ContactsTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ClientContactController extends Controller
{
    use ContactsTrait;

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
        return $this->contacts($client->contacts());
    }


    public function create(Client $client): View
    {
        return view('crm.contacts.create')
            ->with('email', '')
            ->with('route', route('client-contacts.store', $client->ClientID));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request, Client $client): JsonResponse
    {
        $emailConversation = null;
        if ($request->has('conversation')) {
            $emailConversation = EmailConversation::query()->where('Id', $request->conversation)->first();
        }

        try {
            $this->save($client->contacts(), $request->savable());

            if ($emailConversation instanceof  EmailConversation) {
                $emailConversation->update([
                                            'Party'   => Client::getPrimaryKey(),
                                            'PartyID' => $client->ClientID,
                                           ]);

                $emailConversation->emails()->update([
                                                      'Party'   => Client::getPrimaryKey(),
                                                      'PartyID' => $client->ClientID,
                                                     ]);
            }
        } catch (\Throwable | Exception $e) {
            Log::error('Error adding  client Contact. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('contact added successfully.');
    }
}
