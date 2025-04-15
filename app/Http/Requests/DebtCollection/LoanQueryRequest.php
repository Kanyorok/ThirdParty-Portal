<?php

namespace App\Http\Requests\DebtCollection;

use App\Enums\Loan\LoanCategorizationEnum;
use App\Exceptions\ErroredException;
use App\Helpers\StringHelper;
use App\Models\BR\Branch;
use App\Models\BR\Product;
use App\Models\BR\ProductParameter;
use App\Models\BR\UserCodeDetail;
use DateTimeZone;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LoanQueryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->method() === 'POST') {
            return [
                    'Label'   => [
                                  'required',
                                  'string',
                                  'max:255',
                                 ],
                    'Content' => [
                                  'required',
                                  'string',
                                  'max:2000',
                                 ],
                   ];
        }

        return [];
    }

    public function getDated(): ?Carbon
    {
        if (!$this->has('dated') || !StringHelper::isInteger($this->dated) || !((int) $this->dated > 0)) {
            return null;
        }
        try {
            $dated = Carbon::createFromFormat('U', $this->dated);
            $dated?->setTimezone(new DateTimeZone(config('app.timezone')));
            if ($dated instanceof Carbon) {
                return $dated;
            }
        } catch (Exception) {
        }
        return null;
    }

    public function applyFilters(\Illuminate\Database\Query\Builder|Builder $query): Builder|\Illuminate\Database\Query\Builder
    {
        return self::filterDebts($query, $this->getValues());
    }

    public static function filterDebts(\Illuminate\Database\Query\Builder|Builder $query, array $values): Builder|\Illuminate\Database\Query\Builder
    {
        $filters = self::setFilters($values);
        if (array_key_exists('ArrearsDaysMin', $filters) && is_numeric($filters['ArrearsDaysMin']) && array_key_exists('ArrearsDaysMax', $filters) && is_numeric($filters['ArrearsDaysMax'])) {
            if ($filters['ArrearsDaysMin'] === $filters['ArrearsDaysMax']) {
                $query->where('ArrearsDays', $filters['ArrearsDaysMin']);
            } else {
                $query->whereBetween('ArrearsDays', [
                                                     ($filters['ArrearsDaysMin'] < $filters['ArrearsDaysMax']) ? $filters['ArrearsDaysMin'] : $filters['ArrearsDaysMax'],
                                                     ($filters['ArrearsDaysMin'] > $filters['ArrearsDaysMax']) ? $filters['ArrearsDaysMin'] : $filters['ArrearsDaysMax'],
                                                    ]);
            }
        } elseif (array_key_exists('ArrearsDaysMin', $filters) && is_numeric($filters['ArrearsDaysMin'])) {
            $query->where('ArrearsDays', '>=', $filters['ArrearsDaysMin']);
        } elseif (array_key_exists('ArrearsDaysMax', $filters) && is_numeric($filters['ArrearsDaysMax'])) {
            $query->where('ArrearsDays', '<=', $filters['ArrearsDaysMax']);
        }

        if (array_key_exists('Contacted', $filters)) {
            try {
                $Contacted = Carbon::createFromFormat('Y-m-d', $filters['Contacted']);
            } catch (Exception) {
                $Contacted = null;
            }
            if ($Contacted instanceof Carbon) {
                $query->whereDoesntHave('crmsms', function (Builder $query) use ($Contacted) {
                    $query->where('t_SMS.CreatedOn', '>=', $Contacted->startOfDay());
                });
            }
        }

        if (array_key_exists('MaturityMin', $filters)) {
            try {
                $MaturityMin = Carbon::createFromFormat('Y-m-d', $filters['MaturityMin']);
            } catch (Exception) {
                $MaturityMin = null;
            }
            if ($MaturityMin instanceof Carbon) {
                $query->where('MaturityDate', '>=', $MaturityMin->startOfDay());
            }
        }

        if (array_key_exists('MaturityMax', $filters)) {
            try {
                $MaturityMax = Carbon::createFromFormat('Y-m-d', $filters['MaturityMax']);
            } catch (Exception) {
                $MaturityMax = null;
            }
            if ($MaturityMax instanceof Carbon) {
                $query->where('MaturityDate', '<=', $MaturityMax->startOfDay());
            }
        }

        if (array_key_exists('ArrearsAmountMin', $filters) && is_numeric($filters['ArrearsAmountMin']) && array_key_exists('ArrearsAmountMax', $filters) && is_numeric($filters['ArrearsAmountMax'])) {
            if ($filters['ArrearsAmountMin'] === $filters['ArrearsAmountMax']) {
                $query->where('ArrearsAmount', $filters['ArrearsAmountMin']);
            } else {
                $query->whereBetween('ArrearsAmount', [
                                                       ($filters['ArrearsAmountMin'] < $filters['ArrearsAmountMax']) ? $filters['ArrearsAmountMin'] : $filters['ArrearsAmountMax'],
                                                       ($filters['ArrearsAmountMin'] > $filters['ArrearsAmountMax']) ? $filters['ArrearsAmountMin'] : $filters['ArrearsAmountMax'],
                                                      ]);
            }
        } elseif (array_key_exists('ArrearsAmountMin', $filters) && is_numeric($filters['ArrearsAmountMin'])) {
            $query->where('ArrearsAmount', '>=', $filters['ArrearsAmountMin']);
        } elseif (array_key_exists('ArrearsAmountMax', $filters) && is_numeric($filters['ArrearsAmountMax'])) {
            $query->where('ArrearsAmount', '<=', $filters['ArrearsAmountMax']);
        }

        if (array_key_exists('Status', $filters) && is_array($filters['Status']) && ((count($filters['Status']) > 0))) {
            $query->whereIn('Classification', $filters['Status']);
        }
        if (array_key_exists('Branch', $filters) && is_array($filters['Branch']) && ((count($filters['Branch']) > 0))) {
            $query->whereIn('OurBranchID', $filters['Branch']);
        }

        if (array_key_exists('Categorization', $filters)) {
            if (array_key_exists('Product', $filters) && is_array($filters['Product']) && ((count($filters['Product']) > 0))) {
                $products  =  $filters['Product'];
                switch ($filters['Categorization']) {
                    case LoanCategorizationEnum::BOSA->name:
                        $query->whereIn('ProductID', ProductParameter::query()->whereIn('ProductID', $products)->where('SysParamID', LoanCategorizationEnum::BOSA->value)
                            ->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                    case LoanCategorizationEnum::FOSA->name:
                        $query->whereIn('ProductID', ProductParameter::query()->whereIn('ProductID', $products)->where('SysParamID', LoanCategorizationEnum::FOSA->value)
                            ->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                    case LoanCategorizationEnum::MicroLoans->name:
                        $query->whereNotIn('ProductID', ProductParameter::query()->whereIn('ProductID', $products)
                            ->whereIn('SysParamID', [LoanCategorizationEnum::BOSA->value, LoanCategorizationEnum::FOSA->value])->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                }
            } else {
                switch ($filters['Categorization']) {
                    case LoanCategorizationEnum::BOSA->name:
                        $query->whereIn('ProductID', ProductParameter::query()->where('SysParamID', LoanCategorizationEnum::BOSA->value)->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                    case LoanCategorizationEnum::FOSA->name:
                        $query->whereIn('ProductID', ProductParameter::query()->where('SysParamID', LoanCategorizationEnum::FOSA->value)->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                    case LoanCategorizationEnum::MicroLoans->name:
                        $query->whereNotIn('ProductID', ProductParameter::query()->whereIn('SysParamID', [LoanCategorizationEnum::BOSA->value, LoanCategorizationEnum::FOSA->value])->select('ProductID')->pluck('ProductID')->toArray());
                        break;
                }
            }
        } elseif (array_key_exists('Product', $filters) && is_array($filters['Product']) && ((count($filters['Product']) > 0))) {
            $query->whereIn('ProductID', $filters['Product']);
        }

        return $query;
    }

    public static function setFilters(array $values): array
    {
        $filters = collect();
        foreach (['ArrearsDaysMin', 'ArrearsDaysMax', 'Contacted', 'MaturityMin', 'MaturityMax', 'ArrearsAmountMin', 'ArrearsAmountMax'] as $filter) {
            $filters = self::_setFilter($filters, $values, $filter);
        }
        if (array_key_exists('Status', $values)) {
            $states = UserCodeDetail::query()->where('ID', 'LoanSubClassID')->whereIn('SubCodeID', explode(',', $values['Status']))->select('Description')->pluck('Description')->toArray();
            if (count($states) > 0) {
                $filters = $filters->merge(['Status' => $states]);
            }
        }
        if (array_key_exists('Branch', $values)) {
            $branches = Branch::query()->whereIn('OurBranchID', explode(',', $values['Branch']))->select('OurBranchID')->pluck('OurBranchID')->toArray();
            if (count($branches) > 0) {
                $filters = $filters->merge(['Branch' => $branches]);
            }
        }
        if (array_key_exists('Product', $values)) {
            $products = Product::where('ProductTypeID', 'LN')->whereIn('ProductID', explode(',', $values['Product']))->select('ProductID')->pluck('ProductID')->toArray();
            if (count($products) > 0) {
                $filters = $filters->merge(['Product' => $products]);
            }
        }

        if (array_key_exists('Categorization', $values)) {
            try {
                $loanCategorization = LoanCategorizationEnum::valueFromName($values['Categorization']);
                if ($loanCategorization instanceof LoanCategorizationEnum) {
                    $filters = $filters->merge([
                                                'Categorization' => $loanCategorization->name,
                                               ]);
                }
            } catch (ErroredException $e) {
            }
        }

        return $filters->toArray();
    }

    protected static function _setFilter(Collection $filters, array $values, mixed $name): Collection
    {
        if (array_key_exists($name, $values)) {
            return $filters->merge([
                                    $name => $values[$name],
                                   ]);
        }
        return $filters;
    }

    public function getValues(): array
    {
        $values = collect();
        foreach (['ArrearsDaysMin', 'ArrearsDaysMax', 'Contacted', 'MaturityMin', 'MaturityMax', 'ArrearsAmountMin', 'ArrearsAmountMax', 'Status', 'Branch', 'Product', 'Categorization'] as $filter) {
            if (!is_null($this->$filter)) {
                $values->put($filter, $this->$filter);
            }
        }

        return $values->toArray();
    }
}
