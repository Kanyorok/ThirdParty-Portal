<?php

namespace App\Http\Controllers\CRM\Feedback;

use App\Enums\Core\PermissionEnum;
use App\Enums\Feedback\SurveyStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\SurveyRequest;
use App\Models\CRM\Survey;
use App\Services\Feedback\SurveyService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class SurveyController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'show', 'edit']);
        $this->authorizeResource(Survey::class);
    }

    /**
     * Display a listing of the campaigns.
     * @throws Exception
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $actor = $request->user();
            $query = Survey::query()->where(function (Builder $builder) use ($actor) {
                $builder->where('t_Surveys.CreatedBy', $actor->Id)
                    ->where('t_Surveys.Status', SurveyStatusEnum::Draft);
            });

            $status = collect([SurveyStatusEnum::Queued, SurveyStatusEnum::Complete, SurveyStatusEnum::Active]);

            //check for approval.
            if ($actor->hasPermissionTo(PermissionEnum::SurveyApproval->value)) {
                $status->add(SurveyStatusEnum::Approval);
            }


            return Datatables::of($query->orWhereIn('t_Surveys.Status', $status->toArray())->lock('WITH(NOLOCK)')->select('*')->withCount('responses'))->addIndexColumn()
                ->addColumn('action', function (Survey $survey) {
                    return '<a href="' . route('surveys.show', $survey->SurveyID) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> details</button>';
                })->editColumn('Status', function (Survey $survey) {
                    return $survey->Status->description();
                })->editColumn('responses_count', function ($survey) {
                    return number_format($survey->responses_count);
                })->editColumn('CreatedOn', function (Survey $survey) {
                    return $survey->CreatedOn->format('d M, Y h:i A');
                })->editColumn('Notes', function (Survey $survey) {
                    return Str::limit($survey->Notes);
                })->setRowClass('mouse_pointer user-select-none dbl-click-redirect-data')->setRowData([
                                                                                                       'dbl_click_url' => function (Survey $survey) {
                                                                                                        return route('surveys.show', $survey->SurveyID);
                                                                                                       },
                                                                                                      ])->rawColumns(['action'])->make();
        }

        return view('crm.feedback.surveys.index');
    }


    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(SurveyRequest $request): JsonResponse
    {
        $start = $request->getStart();
        $end = $request->getEnd($start);
        $actor = $request->user();

        try {
            $survey = DB::transaction(static function () use ($start, $request, $actor, $end) {
                $survey = SurveyService::create($request->validated('Label'), $start, $end, $actor, $request->validated('Notes') ?? '')->survey;
                activity()->causedBy($actor)->performedOn($survey)->event('create')->log('added a new survey : ' . $survey->SurveyID);
                return $survey;
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create survey :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey created.', route: route('surveys.edit', $survey->SurveyID));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Survey $survey): View
    {
        return view('crm.feedback.surveys.show', compact('survey'))
            ->with('questions', $survey->questions()->with('answers')->get())
            ->with('canApprove', (new SurveyService($survey))->canApprove($request->user()));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Survey $survey): View
    {
        return view('crm.feedback.surveys.edit', compact('survey'))
            ->with('questions', $survey->questions);
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(SurveyRequest $request, Survey $survey): JsonResponse
    {
        $start = $request->getStart($survey->SurveyID);
        $end = $request->getEnd($start, $survey->SurveyID);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($survey, $start, $request, $actor, $end) {
                (new SurveyService($survey))->update($request->validated('Label'), $start, $end, $actor, $request->validated('Notes') ?? '');
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error updating survey (' . $survey->SurveyID . ') :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey updated.', route: route('surveys.edit', $survey->SurveyID));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Survey $survey): JsonResponse
    {
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($survey, $actor) {
                (new SurveyService($survey))->trash($actor);
                activity()->causedBy($actor)->performedOn($survey)->event('delete')->log('deleted survey : ' . $survey->SurveyID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error TRASH survey (' . $survey->SurveyID . ') :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey trashed.', route: route('surveys.index'));
    }
}
