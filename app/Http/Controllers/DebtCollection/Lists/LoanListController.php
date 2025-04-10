<?php

namespace App\Http\Controllers\DebtCollection\Lists;

use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\ListRequest;
use App\Models\BR\Branch;
use App\Models\BR\DebtProduct;
use App\Models\BR\Product;
use App\Models\BR\UserCodeDetail;
use App\Models\MarketingList;
use App\Traits\Controller\MarketingListTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LoanListController extends Controller
{
    use MarketingListTrait;

    public function __construct()
    {
        $this->middleware('ajax')->only(['store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('debt', MarketingList::class);

        if ($request->ajax()) {
            return $this->getMarketingList(MarketingList::query()->where('Source', '=', DebtProduct::getPrimaryKey()), $request->user());
        }

        return view('debt-collection.lists.index');


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ListRequest $request): JsonResponse
    {
        $this->authorize('debt', MarketingList::class);
        $actor = $request->user();
        $visibility = $request->getVisibility();

        try {
            $list = $this->newMarketingList($request->validated('Label'), $actor, MarketingListEnum::Static, $visibility, $request->validated('Notes') ?? '', DebtProduct::getPrimaryKey());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('list added successfully', route: route('loans-list.edit', $list->slug));
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketingList $list)
    {
        $this->authorize('view', $list);
        if ($list->Source !== DebtProduct::getPrimaryKey()) {
            return redirect()->back()->with(['fail' => 'list not found.']);
        }

        try {
            $dated = Carbon::parse(DebtProduct::query()->max('processdate'));
        } catch (Exception) {
            $dated = null;
        }
        $service = $this->service($list);
        return view('debt-collection.lists.show', compact('list', 'dated'))
            ->with('isProcessing', $service->isProcessing())
            ->with('contacts_count', $service->contacts());

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, MarketingList $list)
    {
        $this->authorize('update', $list);

        if ($list->Source !== DebtProduct::getPrimaryKey()) {
            return redirect()->back()->with(['fail' => 'list not found.']);
        }
        try {
            $dated = Carbon::parse(DebtProduct::query()->max('processdate'));
        } catch (Exception $exception) {
            $dated = null;
        }

        return view('debt-collection.lists.edit', compact('list'))
            ->with('dated', $dated)
            ->with('remove', $request->has('remove'))
            ->with('branches', Branch::all(['OurBranchID', 'BranchName']))
            ->with('Products', Product::where('ProductTypeID', 'LN')->get(['ProductID', 'Description']))
            ->with('LoanSubClasses', UserCodeDetail::query()->where('ID', 'LoanSubClassID')->orderBy('DisplayOrder')->get());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ListRequest $request, MarketingList $list): JsonResponse
    {
        $this->authorize('update', $list);
        $visibility = $request->getVisibility();

        if ($list->Source !== DebtProduct::getPrimaryKey()) {
            return $this->errored('list not found.');
        }

        try {
            $list = $this->updateMarketingList($list, $request->string('Label')->toString(), $request->user(), $visibility, $request->string('Notes', '')->toString());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('updated successfully', route: route('loans-list.edit', $list->slug));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MarketingList $list): JsonResponse
    {
        $this->authorize('delete', $list);
        if ($list->Source !== DebtProduct::getPrimaryKey()) {
            return $this->errored('list not found.');
        }

        try {
            $this->deleteMarketingList($list, $request->user());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('list trashed successfully', route: route('loans-list.index'));
    }
}
