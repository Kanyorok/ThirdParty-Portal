<?php

namespace App\Services\Marketing;

use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Models\BR\Account;
use App\Models\BR\Client;
use App\Models\BR\DebtProduct;
use App\Models\Lead;
use App\Models\MarketingList;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListService
{
    public function __construct(public MarketingList $list)
    {
    }

    public function source(): string
    {
        return match ($this->list->Source) {
            DebtProduct::getPrimaryKey() => 'Loans',
            Account::getPrimaryKey() => 'Accounts',
            Client::getPrimaryKey() => 'Members',
            Lead::getPrimaryKey() => 'Leads',
            null => 'Members & Leads',
            default => 'Unknown'
        };
    }

    public function canSource(string $source): bool
    {
        if (($this->list->Source === null || $this->list->Source === $source) && in_array($source, [Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
            return true;
        }

        return ($this->list->Source === $source);
    }

    public function update(string $label, User $actor, VisibilityEnum $visibility, string $notes = ''): static
    {
        $this->list->update([
            'Label' => $label,
            'Notes' => $notes,
            'Visibility' => $visibility->value,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->list)->event('update')->log('updated marketing list ' . $label);

        return $this;
    }

    public function trash(User $actor): static
    {
        $this->list->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id
        ])->save(['timestamps' => false]);

        $this->list->parties()->update([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id
        ]);

        activity()->causedBy($actor)->performedOn($this->list)->event('delete')->log('deleted marketing list ' . $this->list->Label);

        return $this;
    }

    public static function createList(string $label, User $actor, MarketingListEnum $type, VisibilityEnum $visibility, string $notes = '', string $Source = null): ListService
    {
        $list = new MarketingList();
        $list->fill([
            'slug' => Str::slug(Str::limit($label, 70, '') . ' ' . Str::random(7)),
            'Label' => $label,
            'Type' => $type->value,
            'Visibility' => $visibility->value,
            'Notes' => $notes,
            'Source' => $Source,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ])->save();

        activity()->causedBy($actor)->performedOn($list->refresh())->event('create')->log('created ' . $type->name . ' marketing list');

        return (new ListService($list));
    }

    public function removeClients(string|array $ClientIDs, User $actor): static
    {
        if (is_string($ClientIDs)) {
            $ClientIDs = array_map('trim', explode(',', $ClientIDs));
        }

        $this->_removeParty($ClientIDs, Client::getPrimaryKey(), $actor);
        return $this;
    }

    public function addClients(string|array $ClientIDs, User $actor): static
    {
        if (is_string($ClientIDs)) {
            $ClientIDs = array_map('trim', explode(',', $ClientIDs));
        }

        $PartyIDs = Client::query()->where(function (Builder $query) use ($ClientIDs) {
            $query->whereIn('ClientID', $ClientIDs)
                ->whereNotIn('ClientID', $this->list->parties()->where('Party', Client::getPrimaryKey())->select('PartyID'));
        })->select('ClientID')->get('ClientID')->pluck('ClientID')->toArray();


        $this->_attachParty(Parties: $PartyIDs, Party: Client::getPrimaryKey(), actor: $actor);

        return $this;
    }

    public function removeLoans(string|array $AccountIDs, User $actor): static
    {
        if (is_string($AccountIDs)) {
            $AccountIDs = array_map('trim', explode(',', $AccountIDs));
        }

        $this->_removeParty($AccountIDs, DebtProduct::getPrimaryKey(), $actor);
        return $this;
    }

    public function addLoans(string|array $AccountIDs, User $actor): static
    {
        if (is_string($AccountIDs)) {
            $AccountIDs = array_map('trim', explode(',', $AccountIDs));
        }

        $this->_attachParty($AccountIDs, DebtProduct::getPrimaryKey(), $actor);

        return $this;
    }

    protected function _attachParty(array $Parties, string $Party, User $actor): void
    {
        $dated = now();
        $data = collect();
        $parties = collect($Parties)->unique();
        foreach ($parties->chunk(200) as $chunk) {
            foreach ($chunk as $PartyID) {
                $data->add([
                    'MarketingListId' => $this->list->MarketingListID,
                    "Party" => $Party,
                    "PartyID" => $PartyID,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => $dated,
                    'ModifiedOn' => $dated
                ]);
            }

            if ($data->count() > 0) {
                DB::table('t_MarketingListParties')->insert($data->toArray());
                $data = collect();
            }
        }
    }

    public function isProcessing(): bool
    {
        return (is_array($this->list->Processing));
    }

    public function contacts(): int
    {
        if ($this->list->Type->value === MarketingListEnum::Dynamic->value) {
            try {
                return (new DynamicListService($this->list))->query()->count();
            } catch (ErroredException $e) {
            }
            return 0;
        }
        return $this->list->parties()->count();
    }

    protected function _removeParty(array $Parties, string $Party, User $actor): void
    {
        $this->list->parties()->where('Party', $Party)->whereIn('PartyID', $Parties)->update([
            'DeletedBy' => $actor->Id,
            'DeletedOn' => now()
        ]);
    }

    public function addLeads(string|array $LeadIDs, User $actor): static
    {
        if (is_string($LeadIDs)) {
            $LeadIDs = array_map('trim', explode(',', $LeadIDs));
        }

        //this to remove duplicate
        $this->_attachParty(
            Parties: Lead::query()->where(function (Builder $query) use ($LeadIDs) {
                $query->whereIn('LeadID', $LeadIDs)
                    ->whereNotIn('LeadID', $this->list->parties()->where('Party', Lead::getPrimaryKey())->select('PartyID'));
            })->select('LeadID')->get('t_Leads.LeadID')->pluck('LeadID')->toArray(),
            Party: Lead::getPrimaryKey(), actor: $actor);

        return $this;
    }

    public function removeLeads(string|array $LeadIDs, User $actor): static
    {
        if (is_string($LeadIDs)) {
            $LeadIDs = array_map('trim', explode(',', $LeadIDs));
        }

        $this->_removeParty($LeadIDs, Lead::getPrimaryKey(), $actor);
        return $this;
    }
}
