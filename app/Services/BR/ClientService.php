<?php

namespace App\Services\BR;

use App\Enums\EmailPriorityEnum;
use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Services\CRMEmailService;
use App\Services\SMSService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class ClientService
{
    public function __construct(public Client $client)
    {
    }

    /**
     */
    public function isStaff(): bool
    {
        return User::query()->where('ClientID', $this->client->ClientID)->exists();
    }

    public function phoneNo(): ?string
    {
        if (! empty($this->client->Mobile) && Str::length($this->client->Mobile) > 9) {
            return $this->client->Mobile;
        }
        if (! empty($this->client->Phone1) && Str::length($this->client->Phone1) > 9) {
            return $this->client->Phone1;
        }
        if (! empty($this->client->Phone2) && Str::length($this->client->Phone2) > 9) {
            return $this->client->Phone2;
        }

        return null;
    }

    public function sendMessage(string $message, User $actor, string $description = null): SMSService
    {
        $service = SMSService::createClient($this->client, $message, $actor)->send();
        if (is_string($description) && ! empty($description)) {
            $service->addActivity($service->sms->CreatedOn, $description);
        }

        return $service;
    }

    public function getEmail(): ?string
    {
        if (! empty($this->client->Email) && (filter_var($this->client->Email, FILTER_VALIDATE_EMAIL))) {
            return $this->client->Email;
        }

        return null;
    }

    public function sendEmail(string $subject, string $body, User $actor, array $cc = [], EmailPriorityEnum $priorityEnum = null): ?CRMEmailService
    {
        $email = $this->getEmail();
        if (is_null($email)) {
            return null;
        }

        return CRMEmailService::createClient($this->client, $email, $subject, $body, $actor, $cc, ($priorityEnum) ?? EmailPriorityEnum::Normal);
    }

    /**
     * @throws Exception
     */
    public static function dt(Builder $query, array $with = []): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->with(array_merge(['individual', 'corporate'], $with)))->addIndexColumn()
            ->addColumn('action', function (Client $client) {
                return '<a href="' . route('clients.show', $client->ClientID) . '" class="btn btn-outline-info "><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye align-middle me-2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> details</a>';
            })->editColumn('Mobile', function (Client $client) {
                return ((new self($client))->phoneNo()) ?? '-';
            })->addColumn('ClientId', function (Client $client) {
                return $client->ClientID;
            })->addColumn('GovtNo', function (Client $client) {
                return match ($client->ClientTypeID) {
                    'E', 'I', 'G', 'M' => $client->individual?->PassportNo,
                    'CH', 'C', 'JNT' => $client->corporate?->CertificateNo,
                    default => '?',
                };
            })->editColumn('ClientID', function (Client $client) {
                return '<a href="#" data-click_url="' . route('clients.summary', $client->ClientID) . '" data-summary_title="member summary" class="click-summary-data">' . $client->ClientID . '</a>';
            })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                   'dbl_click_url' => function (Client $client) {
                                                                                                       return route('clients.show', $client->ClientID);
                                                                                                   },
                                                                                                  ])->rawColumns(['action', 'ClientID'])->make();
    }

    public static function fromPhone(string $phoneNo): ?self
    {
        $client = self::search(Client::query(), $phoneNo)->lock('WITH(NOLOCK)')->first();
        if ($client instanceof Client) {
            return new self($client);
        }

        return null;
    }

    public static function search(HasMany|Builder $query, string $phoneNo): Builder
    {
        if (Str::startsWith($phoneNo, '+')) {
            $phone1 = $phoneNo;
            $phone2 = Str::replaceFirst('+', '', $phoneNo);
            $phone3 = Str::replaceFirst('+254', '', $phoneNo);
        } elseif (Str::startsWith($phoneNo, '254')) {
            $phone1 = '+' . $phoneNo;
            $phone2 = $phoneNo;
            $phone3 = Str::replaceFirst('+254', '', $phoneNo);
        } else {
            $phone1 = '+' . $phoneNo;
            $phone2 = $phoneNo;
            $phone3 = '+254' . $phoneNo;
        }

        return $query->where(function (Builder $query) use ($phone3, $phone2, $phone1) {
            $query->where('Phone1', $phone1)->orWhere('Phone1', $phone2)->orWhere('Phone1', $phone3)
                ->orWhere('Phone2', $phone1)->orWhere('Phone2', $phone2)->orWhere('Phone2', $phone3)
                ->orWhere('Mobile', $phone1)->orWhere('Mobile', $phone2)->orWhere('Mobile', $phone3);
        });
    }
}
