<?php

namespace App\Http\Controllers\CRM\Marketing\Lists;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ListFilterRequest;
use App\Models\MarketingList;
use App\Models\MarketingListFilter;
use App\Models\SysFilter;
use App\Services\Marketing\DynamicListService;
use App\Services\Marketing\MarketingFilterService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MarketingListFilterController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * List Filters.
     * @throws Exception
     */
    public function index(MarketingList $list): JsonResponse
    {
        $this->authorize('create', MarketingListFilter::class);
        return Datatables::of($list->filters()->lock('WITH(NOLOCK)')->orderBy('t_MarketingListsFilters.DisplayOrder')->with('filter')->get())
            ->addColumn('action', function (MarketingListFilter $listFilter) use ($list) {
                return '<button type="button" class="btn btn-danger btn-sm trash-filter-modal" data-info="' . route('marketing-list-filters.destroy', [$list->slug, $listFilter->Id]) . '~' . $listFilter->filter->FieldName . '-' . $listFilter->filter->Operator->name . '"><i class="fas fa-trash"></i></button>';
            })->editColumn('FilterValues', function (MarketingListFilter $listFilter) {
                if (is_string($listFilter->filter->RelationSource)) {
                    $values = $listFilter->FilterValues;
                    $data = collect();
                    foreach ($values as $value) {
                        $data->add(MarketingFilterService::getSource($listFilter->filter, $value));
                    }
                    return implode(', ', $data->toArray());
                }
                return $listFilter->FilterValues;
            })->addIndexColumn()->rawColumns(['action'])->make();
    }

    /**
     * Add Filter parse the filter.
     * @throws AuthorizationException
     */
    public function create(Request $request, MarketingList $list): View|JsonResponse
    {
        $this->authorize('create', MarketingListFilter::class);

        if (!$request->has('filter')) {
            return $this->errored('unknown filter given.');
        }
        $filter = SysFilter::query()->where('Source', $list->Source)->where('Id', $request->get('filter'))->first();
        if (!$filter instanceof SysFilter) {
            return $this->errored('unknown filter given.');
        }

        return view('crm.marketing.lists.filters.create', compact('list', 'filter'))
            ->with('sources', MarketingFilterService::sources($filter));
    }

    /**
     * Add A filter to list
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function store(ListFilterRequest $request, MarketingList $list): JsonResponse
    {
        $this->authorize('create', MarketingListFilter::class);
        $actor = $request->user();
        $filter = $request->getFilter($list);
        $values = $request->getValue($filter);

        try {
            DB::transaction(static function () use ($list, $filter, $request, $actor, $values) {
                (new DynamicListService($list))->addFilter($filter, $values, $actor, $request->validated('operation'));
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error adding filter :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('filter added successfully.');
    }


    /**
     * Remove Filter from List.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, MarketingList $list, $marketingListFilterId): JsonResponse
    {
        $marketingListFilter = $list->filters()->where('t_MarketingListsFilters.Id', $marketingListFilterId)->first();
        if (!$marketingListFilter instanceof MarketingListFilter) {
            return $this->errored('unknown filter given.');
        }
        $this->authorize('update', $marketingListFilter);

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($marketingListFilter, $list, $actor) {
                (new DynamicListService($list))->rmFilter($marketingListFilter, $actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error removing filter :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('filter removed successfully.');

    }
}
