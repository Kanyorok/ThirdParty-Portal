<?php

namespace App\Traits\Controller;

use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\BR\DebtProduct;
use App\Models\CRM\MarketingList;
use App\Services\Marketing\ListService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\DataTables;

trait MarketingListTrait
{
    public function getMarketingList(Builder|\Illuminate\Database\Eloquent\Builder $query, User $actor): JsonResponse
    {
        $query->where(function ($q) use ($actor) {
            $q->where('Visibility', VisibilityEnum::Public->value)
                ->orWhere(function ($subQuery) use ($actor) {
                    $subQuery->where('Visibility', VisibilityEnum::Private->value)
                        ->where('CreatedBy', $actor->Id);
                });
        });
        try {
            return Datatables::of($query->lock('WITH(NOLOCK)')->select('*')->withCount('parties'))->addIndexColumn()
                ->addColumn('action', function (MarketingList $list) {
                    $url = ($list->Source === DebtProduct::getPrimaryKey()) ? route('loans-list.show', $list->slug) : route('marketing-list.show', $list->slug);
                    return '<a href="' . $url . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('Label', function (MarketingList $list) {
                    $url = ($list->Source === DebtProduct::getPrimaryKey()) ? route('loans-list.show', $list->slug) : route('marketing-list.show', $list->slug);
                    $prepend = $list->Visibility->icon();
                    if ($list->Type->value === MarketingListEnum::Dynamic->value) {
                        $prepend .= '&nbsp;<i class="fas fa-magic-wand-sparkles" title="Dynamic List"></i>';
                    }
                    return $prepend . '&nbsp;<a href="' . $url . '">' . $list->Label . ' </a>';
                })->editColumn('Source', function (MarketingList $list) {
                    return (new ListService($list))->source();
                })->editColumn('LastContacted', function (MarketingList $list) {
                    return $list->LastContacted?->diffForHumans();
                })->editColumn('parties_count', function ($list) {
                    if ($list->Type->value === MarketingListEnum::Dynamic->value) {
                        return number_format((new ListService($list))->contacts());
                    }
                    return number_format($list->parties_count);
                })->editColumn('Notes', function (MarketingList $list) {
                    return Str::limit($list->Notes);
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (MarketingList $list) {
                        return ($list->Source === DebtProduct::getPrimaryKey()) ? route('loans-list.show', $list->slug) : route('marketing-list.show', $list->slug);
                    },
                ])->rawColumns(['Label'])->make();
        } catch (Exception) {
        }

        return $this->errored('fetching data failed, try again later');
    }

    /**
     * @throws ErroredException
     */
    public function newMarketingList(string $label, User $actor, MarketingListEnum $Type, VisibilityEnum $visibility, string $notes = '', string $Source = null): MarketingList
    {
        try {
            return DB::transaction(static function () use ($visibility, $Source, $notes, $label, $actor, $Type) {
                return ListService::createList($label, $actor, $Type, $visibility, $notes, $Source)->list;
            });
        } catch (ErroredException $e) {
            throw new ErroredException($e->getMessage());
        } catch (Exception|Throwable $e) {
            Log::error('Error create list :  ' . $e->getMessage());
            throw new ErroredException('unexpected error, try again later');
        }
    }

    /**
     * @throws ErroredException
     */
    public function updateMarketingList(MarketingList $list, string $label, User $actor, VisibilityEnum $visibility, string $notes = ''): MarketingList
    {
        $service = $this->service($list);
        if ($service->isProcessing()) {
            throw new ErroredException('list is processing');
        }

        try {
            return DB::transaction(static function () use ($service, $visibility, $notes, $label, $actor) {
                return $service->update($label, $actor, $visibility, $notes)->list;
            });
        } catch (ErroredException $e) {
            throw new ErroredException($e->getMessage());
        } catch (Exception|Throwable $e) {
            Log::error('Error update list :  ' . $e->getMessage());
            throw new ErroredException('unexpected error, try again later');
        }
    }

    public function service(MarketingList $list): ListService
    {
        return new ListService($list);
    }

    /**
     * @throws ErroredException
     */
    public function deleteMarketingList(MarketingList $list, User $actor): void
    {
        $service = $this->service($list);
        if ($service->isProcessing()) {
            throw new ErroredException('list is processing');
        }

        try {
            DB::transaction(static function () use ($service, $actor) {
                $service->trash($actor);
            });
        } catch (ErroredException $e) {
            throw new ErroredException($e->getMessage());
        } catch (Exception|Throwable $e) {
            Log::error('Error delete list :  ' . $e->getMessage());
            throw new ErroredException('unexpected error, try again later');
        }
    }
}
