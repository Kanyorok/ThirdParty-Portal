<?php

namespace App\Http\Controllers\CRM\Feedback;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\SurveyQuestionRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Services\Feedback\SurveyService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SurveyQuestionController extends Controller
{

    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function store(SurveyQuestionRequest $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);
        $type = $request->getQuestionType();
        $actor = $request->user();
        if ($survey->questions()->count() > 40) {
            return $this->errored('only 40 questions are allowed');
        }

        try {
            DB::transaction(static function () use ($survey, $request, $actor, $type) {
                (new SurveyService($survey))->addQuestion($type, $request->validated('SurveyQuestion'), $actor, $request->validated('SurveyHelp') ?? '');
                activity()->causedBy($actor)->performedOn($survey)->event('delete')->log('added question to survey : ' . $survey->SurveyID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error add question to survey :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('question add.', route: route('surveys.edit', $survey->SurveyID));
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(SurveyQuestionRequest $request, Survey $survey, string $QuestionId): JsonResponse
    {
        $this->authorize('update', $survey);
        $question = $survey->questions()->where('t_SurveyQuestions.SurveyQuestionId', $QuestionId)->first();
        if (!$question instanceof SurveyQuestion) {
            return $this->errored('invalid question give');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($request, $question, $actor) {
                $question->update([
                    'Question' => $request->validated('SurveyQuestion'),
                    'Notes' => $request->validated('SurveyHelp') ?? '',
                    'ModifiedBy' => $actor->Id,
                ]);
                // activity()->causedBy($actor)->performedOn($survey)->event('delete')->log('added question to survey : ' . $survey->SurveyID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error add question to survey :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('question updated.', route: route('surveys.edit', $survey->SurveyID));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Survey $survey, string $QuestionId): JsonResponse
    {
        $this->authorize('update', $survey);
        $question = $survey->questions()->where('t_SurveyQuestions.SurveyQuestionId', $QuestionId)->first();
        if (!$question instanceof SurveyQuestion) {
            return $this->errored('invalid question give');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($survey, $question, $actor) {
                $question->forceFill([
                    'DeletedBy' => $actor->Id,
                    'DeletedOn' => now()
                ])->save();
                activity()->causedBy($actor)->performedOn($survey)->event('update')->log('removed a question in survey : ' . $survey->SurveyID);
            });
        } catch (Exception $e) {
            Log::error('Error removing question in survey :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('question removed.', route: route('surveys.edit', $survey->SurveyID));
    }
}
