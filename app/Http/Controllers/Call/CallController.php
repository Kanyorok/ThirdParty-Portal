<?php

namespace App\Http\Controllers\Call;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\Contact;
use App\Models\Lead;
use App\Services\BR\ClientService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Str;

class CallController extends Controller
{
    /**
     * A Picked Call with Phone Number
     */
    public function index(Request $request):View
    {
        $data = $request->validate([
            'phone' => ['nullable','string']
        ]);
        $phone = '';
        if (array_key_exists('phone', $data) && !is_null($data['phone'])) {
            $phoneNo = trim($data['phone']);
            $phone = filter_var(Str::of(explode('/', $phoneNo)[0])->replace(['"', '+'], ['', ''])->toString(), FILTER_SANITIZE_NUMBER_INT);
        }

        if (Str::length($phone) < 7) {
            return view('base.calls.index')
                ->with('party', null)
                ->with('phone', null);
        }


        $contact = ClientService::search(Client::query(), $phone)->first();
        if ($contact instanceof Client) {
            return view('base.calls.index')
                ->with('party', $contact)
                ->with('phone', $phone);
        }

        $contact = Lead::query()->where(function (Builder $query) use ($request) {
            $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first();
        if ($contact instanceof Lead) {
            return view('base.calls.index')
                ->with('party', $contact)
                ->with('phone', $phone);
        }

        $contact = Contact::query()->where(function (Builder $query) use ($request) {
            $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first();
        if ($contact instanceof Contact) {
            if ($contact->party instanceof Client) {
                return view('base.calls.index')
                    ->with('party', $contact)
                    ->with('phone', $phone);
            }

            if ($contact->party instanceof Lead) {
                return view('base.calls.index')
                    ->with('party', $contact->party)
                    ->with('phone', $phone);
            }

            return view('base.calls.index')
                ->with('party', $contact)
                ->with('phone', $phone);
        }

        return view('base.calls.index')
            ->with('party', null)
            ->with('phone', $phone);
    }
}
