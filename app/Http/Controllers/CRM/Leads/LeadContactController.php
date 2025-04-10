<?php

namespace App\Http\Controllers\CRM\Leads;

use App\Enums\LeadStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ContactRequest;
use App\Models\EmailConversation;
use App\Models\Lead;
use App\Traits\Controller\ContactsTrait;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LeadContactController extends Controller
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
    public function index(Lead $lead): JsonResponse
    {
        return $this->contacts($lead->contacts());
    }

    public function create(Lead $lead): View|JsonResponse
    {
        if ($lead->Status === LeadStatusEnum::Won->value){
            return $this->errored('lead already won');
        }
        return view('crm.contacts.create')
            ->with('email','')
            ->with('route', route('lead-contacts.store', $lead->LeadID));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request, Lead $lead): JsonResponse
    {
        $emailConversation = null;
        if($request->has('conversation')){
            $emailConversation = EmailConversation::query()->where('Id',$request->conversation)->first();
        }

        try {
            DB::transaction(function () use ($lead, $emailConversation, $request) {
                $this->save($lead->contacts(), $request->savable());

                if ($emailConversation instanceof  EmailConversation ){
                    $emailConversation->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $lead->LeadID,
                    ]);

                    $emailConversation->emails()->update([
                        'Party' => Lead::getPrimaryKey(),
                        'PartyID' => $lead->LeadID,
                    ]);
                }
            });
        } catch (\Throwable|Exception $e) {
            Log::error('Error adding  Lead Contact. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('contact added successfully.');
    }
}
