<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\PartyNoteRequest;
use App\Models\Lead;
use App\Traits\Controller\NotesTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LeadNotesController extends Controller
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
    public function index(Lead $lead): JsonResponse
    {
        return $this->notes($lead->notes());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartyNoteRequest $request, Lead $lead): JsonResponse
    {
        $actor = $request->user();
        $note = $request->getPartyNote();

        try {
            $activity = $this->save($lead->notes(), $note, $actor);
        } catch (Exception $e) {
            Log::error('Error adding  Lead Note. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('new note added successfully.', data: ['activity' => $activity]);
    }
}
