<?php

namespace App\Http\Controllers\Marketing\Competitor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\CompetitorRequest;
use App\Models\Competitor;
use App\Services\LocalityService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
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
                    }
                ])->rawColumns(['CompetitorName', 'photo'])->make();
        }

        return view('marketing.competitors.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompetitorRequest $request): JsonResponse
    {
        $actor = $request->user();
        try {
            $request->save($actor);
        } catch (Exception $e) {
            Log::error('Error adding a competitor ' . $e->getMessage());
            return $this->errored('unexpected error adding competitor, try again latter');
        }

        return $this->succeeded('competitor created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competitor $competitor): View
    {
        $location = (new LocalityService($competitor->location))->getLocation();
        return view('marketing.competitors.show', compact('competitor', 'location'))
            ->with('hasProgress', (is_array($competitor->Processing)));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompetitorRequest $request, Competitor $competitor): JsonResponse
    {
        $actor = $request->user();
        try {
            $request->save($actor, $competitor);
        } catch (\Throwable|Exception $e) {
            Log::error('Error adding a competitor ' . $e->getMessage());
            return $this->errored('unexpected error updating competitor, try again latter');
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
                'DeletedOn' => now()
            ])->save();

            activity()->causedBy($request->user())->performedOn($competitor)->event('delete')->log('Deleted  competitor (' . $competitor->CompetitorID . ').');
        } catch (\Throwable|Exception $e) {
            Log::error('Error adding a competitor ' . $e->getMessage());
            return $this->errored('unexpected error updating competitor, try again latter');
        }

        return $this->succeeded('competitor trashed successfully.', route('competitors.index'));
    }
}
