<?php

namespace App\Http\Controllers\Feedback;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Enums\Feedback\SurveyStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\Feedback\SurveyService;
use App\Traits\Controller\WorkflowTrait;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurveyActionController extends Controller
{
    use WorkflowTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * @throws \Exception|AuthorizationException
     */
    public function workflow(Survey $survey): JsonResponse
    {
        $this->authorize('view', $survey);

        return $this->workflows($survey->workflows());
    }

    /**
     * @throws AuthorizationException
     */
    public function submit(Request $request, string $survey_id): JsonResponse
    {
        $actor = $request->user();
        $survey = Survey::query()->where('SurveyID', $survey_id)->where('CreatedBy', $actor->Id)
            ->where('t_Surveys.Status', SurveyStatusEnum::Draft->value)->first();
        if (!$survey instanceof Survey) {
            return $this->errored('cannot submit, survey not in draft');
        }
        $this->authorize('view', $survey);

        //check if the survey has questions without options
        $id = 1;
        foreach ($survey->questions()->withCount('answers')->get() as $question) {
            if ((int)$question?->answers_count < 1 && $question->Type->value === SurveyQuestionTypeEnum::Closed->value) {
                return $this->errored('Question ' . $id . ' does not have options');
            }
            $id++;
        }

        try {
            DB::transaction(static function () use ($survey, $actor) {
                (new SurveyService($survey))->submit($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error submitting survey failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey submitted successfully.', route('surveys.show', [$survey->SurveyID]));

    }
}
