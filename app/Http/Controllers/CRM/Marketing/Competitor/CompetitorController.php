<?php

namespace App\Http\Controllers\CRM\Marketing\Competitor;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CompetitorRequest;
use App\Models\Core\Country;
use App\Models\ThirdParies\Competitor;
use App\Services\LocalityService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;

class CompetitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show']);
        $this->authorizeResource(Competitor::class);
    }

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of(Competitor::query()->lock('WITH(NOLOCK)')->with(['location', 'photo'])->select('*'))->addIndexColumn()
                ->editColumn('photo', function (Competitor $competitor) {
                    return $competitor->getImage('class="img-thumbnail" style="height: 70px;"');
                })->editColumn('CompetitorName', function (Competitor $competitor) {
                    return '<a href="' . route('competitors.show', $competitor->CompetitorID) . '">' . $competitor->CompetitorName . '</a>';
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                    'dbl_click_url' => function (Competitor $competitor) {
                        return route('competitors.show', $competitor->CompetitorID);
                    },
                ])->rawColumns(['CompetitorName', 'photo'])->make();
        }

        return view('crm.marketing.competitors.index')
            ->with('Countries', Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompetitorRequest $request): JsonResponse
    {
        $actor = $request->user();

        try {
            $request->save($actor);
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('competitor created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competitor $competitor): View
    {
        $competitor->load(['location', 'photo', 'country']);

        return view('crm.marketing.competitors.show', [
            'competitor' => $competitor,
            'location' => (new LocalityService($competitor->location))->getLocation(),
            'Countries' => Country::query()->select(['Name', 'CountryCode', 'Id', 'PhoneCode', 'Flag'])->whereHas('localities')->orderBy('t_Countries.Name')->get(),
            'hasProgress' => (is_array($competitor->Processing)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompetitorRequest $request, Competitor $competitor): JsonResponse
    {
        $actor = $request->user();

        try {
            $request->save($actor, $competitor);
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('competitor updated successfully.', route('competitors.show', [$competitor->CompetitorID]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Competitor $competitor): JsonResponse
    {
        try {
            $competitor->forceFill([
                'DeletedBy' => $request->user()->Id,
                'DeletedOn' => now(),
            ])->save();

            activity()->causedBy($request->user())->performedOn($competitor)->event('delete')->log('Deleted  competitor (' . $competitor->CompetitorID . ').');
        } catch (Throwable | Exception $e) {
            Log::error('Error adding a competitor ' . $e->getMessage());

            return $this->errored('unexpected error updating competitor, try again latter');
        }

        return $this->succeeded('competitor trashed successfully.', route('competitors.index'));
    }
}
