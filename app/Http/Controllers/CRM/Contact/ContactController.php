<?php

namespace App\Http\Controllers\CRM\Contact;

use App\Enums\CallStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Contact;
use App\Models\Schedule;
use App\Traits\Controller\ContactsTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ContactsTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->contacts(Contact::query()->where('PartyID', '0'));
        }


        return view('crm.contacts.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, Contact $contact)
    {
        if ($contact->PartyID !== '0') {
            return redirect()->back()->with(['fail' => 'contact not found or invalid']);
        }
        $call = null;
        $schedule = null;
        if (is_numeric($request->call)) {
            $call = Call::query()->where('CallStatusID', CallStatusEnum::SuccessOngoing->value)->where('CallID', $request->get('call'))
                ->where('PartyID', $contact->ContactID)->where('Party', Contact::getPrimaryKey())
                ->whereBetween('StartOn', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
                ->lock('WITH(NOLOCK)')->first();
            if ($call instanceof Call) {
                activity()->causedBy($request->user())->performedOn($call)->event('start')->log('started a call with contact ' . $contact->ContactID);
                $schedule = ($call?->schedule instanceof Schedule) ? $call->schedule : null;
            } else {
                $call = null;
            }
        }

        activity()->causedBy($request->user())->performedOn($contact)->event('view')->log('Viewed Unattached contact details');
        return view('crm.contacts.show', compact('contact', 'schedule', 'call'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        //
    }
}
