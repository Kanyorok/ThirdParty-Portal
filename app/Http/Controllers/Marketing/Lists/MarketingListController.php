<?php

namespace App\Http\Controllers\Marketing\Lists;

use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ListRequest;
use App\Models\BR\DebtProduct;
use App\Models\MarketingList;
use App\Models\SysFilter;
use App\Traits\Controller\MarketingListTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarketingListController extends Controller
{
    use MarketingListTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show', 'edit', 'create']);
    }

    /**
     * List of Lists
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', MarketingList::class);
        if ($request->ajax()) {
            return $this->getMarketingList(MarketingList::query()->where('Source', '!=', DebtProduct::getPrimaryKey()), $request->user());
        }

        return view('marketing.lists.index');
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function store(ListRequest $request): JsonResponse
    {
        $this->authorize('create', MarketingList::class);
        $actor = $request->user();
        $visibility = $request->getVisibility();
        $Type = $request->getType();
        $Source = $request->getSource($Type);

        try {
            $list = $this->newMarketingList($request->string('Label')->toString(), $actor, $Type, $visibility, $request->string('Notes')->toString(), $Source);
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('list added successfully', route: route('marketing-list.edit', $list->slug));

    }

    /**
     * Display the specified resource.
     * @throws AuthorizationException
     */
    public function show(MarketingList $list): RedirectResponse|View
    {
        $this->authorize('view', $list);

        if ($list->Source === DebtProduct::getPrimaryKey()) {
            return redirect()->back()->with(['fail' => 'list not found.']);
        }

        $service = $this->service($list);
        return view('marketing.lists.show', compact('list'))
            ->with('isProcessing', $service->isProcessing())
            ->with('contacts_count', $service->contacts());
    }

    /**
     * Show the form for editing the specified resource.
     * @throws AuthorizationException
     */
    public function edit(MarketingList $list): RedirectResponse|View
    {
        $this->authorize('update', $list);

        if ($list->Source === DebtProduct::getPrimaryKey()) {
            return redirect()->back()->with(['fail' => 'list not found.']);
        }

        if ($list->Type->value === MarketingListEnum::Dynamic->value) {
            return view('marketing.lists.edit-dynamic', compact('list'))
                ->with('filters', SysFilter::query()->where('Source', $list->Source)->get(['Id', 'Name', 'Operator']));
        }

        if ($this->service($list)->isProcessing()) {
            return redirect()->back()->with(['fail' => 'list is currently processing']);
        }

        return view('marketing.lists.edit-static', compact('list'));
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(ListRequest $request, MarketingList $list): JsonResponse
    {
        $this->authorize('update', $list);
        $visibility = $request->getVisibility();

        if ($list->Source === DebtProduct::getPrimaryKey()) {
            return $this->errored('list not found.');
        }

        try {
            $list = $this->updateMarketingList($list, $request->string('Label')->toString(), $request->user(), $visibility, $request->string('Notes', '')->toString());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('updated successfully', route: route('marketing-list.edit', $list->slug));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MarketingList $list): JsonResponse
    {
        $this->authorize('delete', $list);
        if ($list->Source === DebtProduct::getPrimaryKey()) {
            return $this->errored('list not found.');
        }

        try {
            $this->deleteMarketingList($list, $request->user());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('list trashed successfully', route: route('marketing-list.index'));
    }
}
