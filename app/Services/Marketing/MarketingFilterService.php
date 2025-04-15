<?php

namespace App\Services\Marketing;

use App\Enums\Core\ComparisonOperatorsEnum;
use App\Enums\GenderEnum;
use App\Enums\LeadStatusEnum;
use App\Enums\LeadTypeEnum;
use App\Models\BR\Branch;
use App\Models\BR\SystemCodeDetail;
use App\Models\MarketingListFilter;
use App\Models\SysFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MarketingFilterService
{
    private SysFilter $_filter;

    public function __construct(public MarketingListFilter $listFilter)
    {
        $this->_filter = $this->listFilter->filter;
    }

    public function query(Builder $query): Builder
    {
        if (is_string($this->_filter->Relation)) {
            if ($this->_filter->Relation === 'enum') {
                return $query->whereIn($this->_filter->FieldName, $this->listFilter->FilterValues, $this->listFilter->After);
            }

            return $query->whereHas($this->_filter->Relation, function (Builder $query) {
                if ($this->_filter->Operator->value === ComparisonOperatorsEnum::In->value) {
                    return $query->lock('WITH(NOLOCK)')->whereIn($this->_filter->FieldName, $this->listFilter->FilterValues, $this->listFilter->After);
                }

                if ($this->_filter->Operator->value === ComparisonOperatorsEnum::Between->value) {
                    return $query->lock('WITH(NOLOCK)')->whereBetween($this->_filter->FieldName, $this->listFilter->FilterValues, $this->listFilter->After);
                }
                //if ($this->_filter->Operator->isBasic()) {
                return $query->lock('WITH(NOLOCK)')->where($this->_filter->FieldName, $this->_filter->Operator->symbol(), $this->listFilter->FilterValue, $this->listFilter->After);
                // }
            });
        }


        if ($this->_filter->Operator->isBasic()) {
            return $query->where($this->_filter->FieldName, $this->_filter->Operator->symbol(), $this->listFilter->FilterValue, $this->listFilter->After);
        }

        if ($this->_filter->Operator->value === ComparisonOperatorsEnum::Between->value) {
            return $query->whereBetween($this->_filter->FieldName, $this->listFilter->FilterValues, $this->listFilter->After);
        }

        return $query;
    }

    public function getSources(): Collection
    {
        return self::sources($this->_filter);
    }


    public static function sources(SysFilter $filter): Collection
    {
        return match ($filter->RelationSource) {
            'OurBranchID' => Branch::query()->select(['OurBranchID as value', 'BranchName as name'])->get(),
            'BR_SystemCodeDetail' => SystemCodeDetail::query()->where('ID', $filter->FieldName)->select(['SubCodeID as value', 'Description as name'])->get(),
            'enum' => match ($filter->FieldName) {
                'Gender' => GenderEnum::getAll(),
                'Status' => LeadStatusEnum::getAll(),
                'Type' => LeadTypeEnum::getAll(),
                default => collect(),
            },
            default => collect(),
        };
    }

    public static function sourceValue(SysFilter $filter, mixed $value): bool
    {
        $sources = self::sources($filter);
        foreach ($sources as $source) {
            if ($source->value === $value) {
                return true;
            }
        }
        return false;
    }

    public static function getSource(SysFilter $filter, mixed $value)
    {
        $sources = self::sources($filter);
        foreach ($sources as $source) {
            if ($source->value === $value) {
                return $source->name;
            }
        }
        return $value;
    }
}
