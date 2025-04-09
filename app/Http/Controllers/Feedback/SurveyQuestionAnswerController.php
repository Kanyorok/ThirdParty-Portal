<?php

namespace App\Http\Controllers\Feedback;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class SurveyQuestionAnswerController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }
    /**
     * Display a listing of the resource.
     * @throws AuthorizationException
     */
    public function index(SurveyQuestion $question): JsonResponse
    {
        $this->authorize('update', $question?->survey);

        return Datatables::of($question->answers()->lock('WITH(NOLOCK)')->select('*'))->addIndexColumn()
            ->addColumn('action', function (SurveyQuestionAnswer $answer) use ($question) {
                return '<button type="button" class="btn btn-danger btn-sm trash-question-option" data-info="' . route('survey-question-answer.destroy', [$question->SurveyQuestionId, $answer->Id]) . '~' . $answer->Answer . '"><i class="fas fa-trash"></i></button>';
            })->editColumn('Answer', function (SurveyQuestionAnswer $answer) {
                return (empty($answer->Notes))
                    ? $answer->Answer
                    : '<details><summary>' . $answer->Answer . '</summary><p>' . $answer->Notes . '</p></details>';

            })->rawColumns(['action', 'Answer'])->make();
    }

    /**
     * @throws AuthorizationException
     */
    public function five(Request $request, SurveyQuestion $question): JsonResponse
    {
        $this->authorize('update', $question?->survey);

        if ($question->Type->value !== SurveyQuestionTypeEnum::Closed->value) {
            return $this->errored('Question has to be closed ended.');
        }
        if ($question->answers()->exists()) {
            return $this->errored('Question has options delete all first.');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($question, $request, $actor) {
                $question->answers()->create(['Answer' => '1. One', 'Notes' => "", 'CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id,]);
                $question->answers()->create(['Answer' => '2. Two', 'Notes' => "", 'CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id,]);
                $question->answers()->create(['Answer' => '3. Three', 'Notes' => "", 'CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id,]);
                $question->answers()->create(['Answer' => '4. Four', 'Notes' => "", 'CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id,]);
                $question->answers()->create(['Answer' => '5. Five', 'Notes' => "", 'CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id,]);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error add option 1-6  to question (' . $question->SurveyQuestionId . ') :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('options added.', data: [
            'question' => $question->SurveyQuestionId
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function boolean(Request $request, SurveyQuestion $question): JsonResponse
    {
        $this->authorize('update', $question?->survey);

        if ($question->Type->value !== SurveyQuestionTypeEnum::Closed->value) {
            return $this->errored('Question has to be closed ended.');
        }
        if ($question->answers()->exists()) {
            return $this->errored('Question has options delete all first.');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($question, $actor) {
                $question->answers()->create([
                    'Answer' => "Yes",
                    'Notes' => 'Agree',
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);
                $question->answers()->create([
                    'Answer' => "No",
                    'Notes' => 'Disagree',
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error add option (yes/no) to question (' . $question->SurveyQuestionId . ') :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('options added.', data: [
            'question' => $question->SurveyQuestionId
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(Request $request, SurveyQuestion $question): JsonResponse
    {
        $this->authorize('update', $question?->survey);
        $request->validate([
            'QuestionOption' => ['required', 'max:500'],
            'QuestionOptionHelp' => ['nullable', 'max:500'],
        ]);

        if ($question->Type->value !== SurveyQuestionTypeEnum::Closed->value) {
            return $this->errored('Question has to be closed ended.');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($question, $request, $actor) {
                $question->answers()->create([
                    'Answer' => $request->get('QuestionOption'),
                    'Notes' => $request->get('QuestionOptionHelp'),
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error add option to question (' . $question->SurveyQuestionId . ') :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('option added.', data: [
            'question' => $question->SurveyQuestionId
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, SurveyQuestion $question, $surveyQuestionAnswer): JsonResponse
    {
        $this->authorize('update', $question?->survey);
        $option = $question->answers()->where('t_SurveyQuestionAnswers.Id', $surveyQuestionAnswer)->first();
        if (!$option instanceof SurveyQuestionAnswer) {
            return $this->errored('Option does not exist');
        }

        $actor = $request->user();
        try {
            DB::transaction(static function () use ($option, $actor) {
                $option->forceFill([
                    'DeletedBy' => $actor->Id,
                    'DeletedOn' => now()
                ])->save();
                //activity()->causedBy($actor)->performedOn($survey)->event('update')->log('removed a question in survey : ' . $survey->SurveyID);
            });
        } catch (Exception $e) {
            Log::error('Error removing option :  ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('option removed.', data: [
            'question' => $question->SurveyQuestionId
        ]);
    }
}
