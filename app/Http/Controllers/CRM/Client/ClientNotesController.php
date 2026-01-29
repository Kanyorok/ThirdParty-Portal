<?php

namespace App\Http\Controllers\CRM\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\PartyNoteRequest;
use App\Models\BR\Client;
use App\Traits\Controller\NotesTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ClientNotesController extends Controller
{
    use NotesTrait;

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
        return $this->notes($client->notes());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartyNoteRequest $request, Client $client): JsonResponse
    {
        $note = $request->getPartyNote();
        $actor = $request->user();

        try {
            $activity = $this->save($client->notes(), $note, $actor);
        } catch (Exception $e) {
            Log::error('Error adding Client Note. e: ' . $e->getMessage());

            return $this->errored('unexpected error try again latter');
        }

        return $this->succeeded('new note added successfully.', data: ['activity' => $activity]);
    }
}
