<?php

namespace App\Traits\Controller;

use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Contact;
use App\Models\CRM\Lead;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\DataTables;

trait ContactsTrait
{
    /**
     * @throws Exception
     */
    public function contacts(Builder|MorphMany|HasMany|\Illuminate\Database\Eloquent\Builder $query): JsonResponse
    {
        return Datatables::of($query->lock('WITH(NOLOCK)')->with('party')->select('*'))->addIndexColumn()
            ->addColumn('action', function (Contact $contact) {
                if ($contact->PartyID === '0') {
                    return '<a href="' . route('unattached.contacts.show', [$contact->ContactID]) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</a>';
                }
                return '<button type="button"  data-click_url="' . route('contacts.show', [$contact->ContactID]) . '" data-summary_title="contact details" class="btn btn-info btn-sm click-summary-data"><i class="fas fa-eye"></i> details</button>';
            })->editColumn('Email', function (Contact $contact) {
                if (!filter_var($contact->Email, FILTER_VALIDATE_EMAIL)) {
                    return '';
                }

                if ($contact->party instanceof Lead) {
                    return '<a  href="javascript:void(0)" data-info="' . route('lead-mail.store', [$contact->party->LeadID]) . '~' . $contact->Label . '~' . $contact->Email . '"
                               class="btn btn-lg btn-link me-1 my-1 send-mail-to-action">' . $contact->Email . '</a>';
                }
                if ($contact->party instanceof Client) {
                    return '<a  href="javascript:void(0)" data-info="' . route('client-mail.store', [$contact->party->ClientID]) . '~' . $contact->Label . '~' . $contact->Email . '"
                               class="btn btn-lg btn-link me-1 my-1 send-mail-to-action">' . $contact->Email . '</a>';
                }
                /* if ($contact->party instanceof Client){

                 }*/
                return $contact->Email;
            })->editColumn('Phone', function (Contact $contact) {

                if (Str::length($contact->Phone) < 2) {
                    return '';
                }

                if ($contact->party instanceof Lead) {
                    return '<div class="btn-group">
                            <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-link dropdown-toggle">
                                ' . $contact->Phone . '
                            </button>
                            <div class="dropdown-menu" style="">
                                <a class="dropdown-item disabled text-decoration-line-through" href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                   data-info="' . route('lead-sms.store', [$contact->party->LeadID]) . '~' . $contact->Label . '~' . $contact->Phone . '">
                                    <i class="fas fa-message"></i> Message</a>
                            </div>
                        </div>';
                }

                if ($contact->party instanceof Client) {
                    return '<div class="btn-group">
                            <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-link dropdown-toggle">
                                ' . $contact->Phone . '
                            </button>
                            <div class="dropdown-menu" style="">
                                <a class="dropdown-item disabled text-decoration-line-through" href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                   data-info="' . route('lead-sms.store', [$contact->party->ClientID]) . '~' . $contact->Label . '~' . $contact->Phone . '">
                                    <i class="fas fa-message"></i> Message</a>
                            </div>
                        </div>';
                }
                /* if ($contact->party instanceof Client){

                 }*/
                return  $contact->Phone;
            })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                'dbl_click_url' => function (Contact $contact) {
                    return route('contacts.show', [$contact->ContactID]);
                },
                'summary_title' => 'contact details',
            ])->rawColumns(['action', 'Email', 'Phone'])->make();
    }

    /**
     * @throws Throwable
     */
    public function change(Contact $contact, array $data): void
    {
        DB::transaction(static function () use ($contact, $data) {
            $contact->fill($data)->save();

            activity()->causedBy(auth()->user())->performedOn($contact)->event('update')->log('updated contact details');
        });
    }

    /**
     * @throws Throwable
     */
    public function save(MorphMany $query, array $data): void
    {
        DB::transaction(static function () use ($data, $query) {
            $query->create($data);
        });
    }

    public function trash(Contact $contact, User $actor): void
    {
        $contact->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id,
        ])->save(['timestamps' => false]);
    }
}
