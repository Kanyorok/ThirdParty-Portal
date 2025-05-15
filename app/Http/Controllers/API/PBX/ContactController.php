<?php

namespace App\Http\Controllers\API\PBX;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use App\Services\BR\ClientService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Str;

class ContactController extends Controller
{
    /**
     * Search Contact
     */
    public function index(Request $request): JsonResponse
    {
        //\Log::info('3cx info request: ' . json_encode($request->all()));

        if (!$request->has('phone')) {
            return $this->response([
                                    'id'          => null,
                                    'name'        => null,
                                    'phone'       => null,
                                    'active'      => false,
                                    'description' => null,
                                   ]);
        }

        $phone = str_replace('"', '', $request->get('phone'));

        if (Str::length($phone) < 7) {
            return $this->response([
                                    'id'          => null,
                                    'name'        => null,
                                    'phone'       => null,
                                    'active'      => false,
                                    'description' => null,
                                   ]);
        }

        $contact = ClientService::search(Client::query(), $phone)->select(['ClientID', 'Name', 'Mobile', 'Phone1', 'Phone2', 'ClientStatusID'])->first();
        if ($contact instanceof Client) {
            return $this->response([
                                    'id'          => $contact->ClientID,
                                    'name'        => $contact->Name . ' (M)',
                                    'phone'       => (new ClientService($contact))->phoneNo(),
                                    'active'      => ($contact->ClientStatusID === 'A'),
                                    'description' => "member",
                                   ], $request->get('phone'));
        }

        $contact = Lead::withTrashed()->where(function (Builder $query) use ($request) {
                $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first(["Name", "Phone", "LeadID", "DeletedOn"]);
        if ($contact instanceof Lead) {
            return $this->response([
                                    'id'          => $contact->LeadID,
                                    'name'        => $contact->Name . '(L)',
                                    'phone'       => $contact->Phone,
                                    'active'      => (!$contact->trashed()),
                                    'description' => 'lead',
                                   ], $request->get('phone'));
        }

        $contact = Contact::query()->where(function (Builder $query) use ($request) {
                $query->where('Phone', $request->get('phone'))->orWhere('Phone', '+' . $request->get('phone'));
        })->first(['ContactID', 'Label', 'Phone']);
        if ($contact instanceof Contact) {
            if ($contact->party instanceof Lead) {
                return $this->response([
                                        'id'          => $contact->ContactID,
                                        'name'        => $contact->Label . '(L)',
                                        'phone'       => $contact->Phone,
                                        'active'      => true,
                                        'description' => 'lead',
                                       ], $request->get('phone'));
            }
            if ($contact->party instanceof Client) {
                return $this->response([
                                        'id'          => $contact->party->ClientID,
                                        'name'        => $contact->party->Name . ' (M)',
                                        'phone'       => $contact->Phone,
                                        'active'      => ($contact->ClientStatusID === 'A'),
                                        'description' => "member",
                                       ], $request->get('phone'));
            }

            return $this->response([
                                    'id'          => $contact->ContactID,
                                    'name'        => $contact->Label . '(L)',
                                    'phone'       => $contact->Phone,
                                    'active'      => true,
                                    'description' => 'lead',
                                   ], $request->get('phone'));
        }

        //create contact and redirect to it.
        return $this->response([
                                'id'          => null,
                                'name'        => null,
                                'phone'       => null,
                                'active'      => false,
                                'description' => null,
                               ]);
    }

    private function response(array $resource, string $mobile = null): JsonResponse
    {
        return response()->json([
                                 [
                                  'active'        => $resource['active'],
                                  'address'       => null,
                                  'description'   => $resource['description'],
                                  'email'         => null,
                                  'id'            => $resource['id'],
                                  'job_title'     => null,
                                  'language'      => 'en',
                                  'mobile'        => $mobile,
                                  'name'          => $resource['name'],
                                  'phone'         => $resource['phone'],
                                  'time_zone'     => 'nairobi',
                                  'twitter_id'    => \Str::random(8),
                                  'custom_fields' => [],
                                  'facebook_id'   => route('call.incoming.start', ['phone' => $mobile]),
                                 ],
                                ]);
    }

    /**
     * Save Contact
     */
    public function store(Request $request): JsonResponse
    {
        Log::warning('Create Contact');
        Log::info(json_encode($request->all()));
        return $this->succeeded('ok');
    }
}
