<?php

namespace App\Services\Marketing;

use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Models\BR\Client;
use App\Models\Lead;
use App\Models\MarketingList;
use App\Models\MarketingListFilter;
use App\Models\SysFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DynamicListService
{
    public function __construct(public MarketingList $list)
    {
    }

    /**
     * @throws ErroredException
     */
    public function addFilter(SysFilter $filter, string|array $values, User $actor, string $boolean = 'and'): static
    {
        if (!in_array($boolean, ['and', 'or'])) {
            throw new ErroredException('operation should be either `and` / `or`');
        }

        if ($this->list->Type?->value !== MarketingListEnum::Dynamic->value) {
            throw new ErroredException('Filters are only added to Dynamic Lists.');
        }

        $this->list->filters()->create([
            'FilterId' => $filter->Id,
            'FilterValue' => is_array($values) ? null : $values,
            'FilterValues' => is_array($values) ? $values : null,
            'After' => $boolean,
            'DisplayOrder' => $this->list->filters()->count() + 1,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($this->list)->event('create')->log('added filter on (' . $filter->FieldName . ') in a marketing list');

        return $this;
    }


    public function rmFilter(MarketingListFilter $listFilter, User $actor): static
    {
        $listFilter->forceFill([
            'DeletedOn' => now(),
            'DeletedBy' => $actor->Id
        ])->save(['timestamps' => false]);

        activity()->causedBy($actor)->performedOn($this->list)->event('delete')->log('delete filter on (' . $listFilter->filter->FieldName . ') in a marketing list');

        return $this;
    }


    /**
     * @throws ErroredException
     */
    public function query(): Builder
    {
        $query = $this->_query();
        $ListFilters = $this->list->filters()->lock('WITH(NOLOCK)')->orderBy('t_MarketingListsFilters.DisplayOrder')->with(['filter'])->get();
        foreach ($ListFilters as $ListsFilter) {
            $query = (new MarketingFilterService($ListsFilter))->query($query);
        }

        return $query;
    }

    /**
     * @throws ErroredException
     */
    private function _query(): Builder
    {
        if ($this->list->Source === Client::getPrimaryKey()) {
            return Client::query();
        }
        if ($this->list->Source === Lead::getPrimaryKey()) {
            return Lead::query();
        }
        throw new ErroredException('Unknown source.');
    }


    /*   public function setOrder(User $actor, int $position = null): static
       {
           if (is_int($position)) {
               $ordID = $position;
               $this->codeDetail->update(['DisplayOrder' => $ordID, 'ModifiedBy' => $actor->Id]);
               $others = CodeDetail::query()->where('CodeID', $this->codeDetail->CodeID)->where('DisplayOrder', '>=', $ordID)->get();
               foreach ($others as $other) {
                   $ordID++;
                   $other->update(['DisplayOrder' => $ordID, 'ModifiedBy' => $actor->Id]);
               }

               return $this;
           }

           return $this->setOrder($actor, (CodeDetail::query()->where('CodeID', $this->codeDetail->CodeID)->count() + 1));
       }*/
}
