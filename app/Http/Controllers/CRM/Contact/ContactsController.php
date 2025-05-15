<?php

namespace App\Http\Controllers\CRM\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ContactRequest;
use App\Models\CRM\Contact;
use App\Traits\Controller\ContactsTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContactsController extends Controller
{
    use ContactsTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    public function create()
    {
        return view('crm.contacts.create');
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Contact $contact): View
    {
        activity()->causedBy($request->user())->performedOn($contact)->event('view')->log('Viewed contact details');
        return view('crm.contacts.summary')->with('contact', $contact)
            ->with('party', $contact->party);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        try {
            $this->change($contact, $request->savable(true));
        } catch (Exception $e) {
            Log::error('Error updating contact. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('contact updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        try {
            $this->trash($contact, $request->user());
        } catch (Exception $e) {
            Log::error('Error delete contact. e: ' . $e->getMessage());
            return $this->errored('unexpected error, try again latter');
        }

        return $this->succeeded('trashed successfully.');
    }
}
