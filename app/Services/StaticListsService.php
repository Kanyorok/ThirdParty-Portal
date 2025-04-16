<?php

namespace App\Services;

use App\Models\CodeDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StaticListsService
{
    public function __construct(public CodeDetail $codeDetail)
    {
    }

    public static function create(string $list, string $description, User $actor): StaticListsService
    {
        $detail = new CodeDetail();
        $detail->fill([
                       'CodeID'      => $list,
                       'Description' => $description,
                       'CreatedBy'   => $actor->Id,
                       'ModifiedBy'  => $actor->Id,
                      ])->save();

        activity()->causedBy($actor)->performedOn($detail->refresh())->event('create')->log('created ' . $detail->CodeID);

        return (new self($detail))->setOrder($actor);
    }

    public function setOrder(User $actor, int $position = null): static
    {
        if (is_int($position)) {
            $newPosition = $position;
            $oldPosition = $this->codeDetail->DisplayOrder;
            if (!is_int($oldPosition)) {
                $this->codeDetail->update(['DisplayOrder' => $newPosition, 'ModifiedBy' => $actor->Id]);
                return $this;
            }

            /*  if ($oldPosition === $newPosition){
                  return $this;
              }*/

            if ($oldPosition < $newPosition) {
                $others = CodeDetail::query()->where('CodeID', $this->codeDetail->CodeID)->where(function (Builder $q) use ($oldPosition, $newPosition) {
                    $q->where('DisplayOrder', '<=', $newPosition)->where('DisplayOrder', '>', $oldPosition);
                })->orderBy('DisplayOrder')->where('ID', '!=', $this->codeDetail->ID)->get();
                foreach ($others as $other) {
                    $other->update(['DisplayOrder' => bcsub($other->DisplayOrder, 1), 'ModifiedBy' => $actor->Id]);
                }
                $this->codeDetail->update(['DisplayOrder' => $newPosition, 'ModifiedBy' => $actor->Id]);
                return $this;
            }

            if ($oldPosition > $newPosition) {
                $others = CodeDetail::query()->where('CodeID', $this->codeDetail->CodeID)->where(function (Builder $q) use ($oldPosition, $newPosition) {
                    $q->where('DisplayOrder', '<', $oldPosition)->where('DisplayOrder', '>=', $newPosition);
                })->orderBy('DisplayOrder')->where('ID', '!=', $this->codeDetail->ID)->get();
                foreach ($others as $other) {
                    $other->update(['DisplayOrder' => bcadd($other->DisplayOrder, 1), 'ModifiedBy' => $actor->Id]);
                }
                $this->codeDetail->update(['DisplayOrder' => $newPosition, 'ModifiedBy' => $actor->Id]);
                return $this;
            }


            return $this;
        }

        return $this->setOrder($actor, (CodeDetail::query()->where('CodeID', $this->codeDetail->CodeID)->count()));
    }

    public const string MarketingModes = 'MarketingModes';
    public const string CustomerResponses = 'CustomerResponses';
    public const string ProductDevelopmentStages = 'ProductDevelopmentStages';
    public const string LeadLossReason = 'LeadLossReason';
    public const string Industries = 'Industries';
    public const string CustomerType = 'CustomerTypes';
    public const string TicketCategories = 'TicketCategories';


    public static function getLists(): Collection
    {
        return collect([
                        self::MarketingModes,
                        self::CustomerResponses,
                        self::LeadLossReason,
                        self::Industries,
                        self::CustomerType,
                        self::TicketCategories,
                        self::ProductDevelopmentStages,
                       ]);
    }

    public static function getIndustries(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getList(self::Industries);
    }

    public static function getList(string|array $CodeIDs): \Illuminate\Database\Eloquent\Collection
    {
        /*$q = self::_query();
        if (is_string($CodeIDs)) {
            $q->where('CodeID', $CodeIDs);
        }

        if (is_array($CodeIDs)) {
            $q->whereIn('CodeID', $CodeIDs);
        }

        return $q->get();*/
        return self::getRawList($CodeIDs)->get();
    }

    public static function getRawList(string|array $CodeIDs): Builder
    {
        $q = self::_query();
        if (is_string($CodeIDs)) {
            $q->where('CodeID', $CodeIDs);
        }

        if (is_array($CodeIDs)) {
            $q->whereIn('CodeID', $CodeIDs);
        }
        return $q;
    }


    protected static function _query(): Builder
    {
        return CodeDetail::query()->where('IsActive', true)->orderBy('DisplayOrder');
    }
}
