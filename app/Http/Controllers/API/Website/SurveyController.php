<?php

namespace App\Http\Controllers\API\Website;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Feedback\SurveyQuestionsCollection;
use App\Models\SurveyQuestionAnswer;
use App\Services\Feedback\SurveyService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{
    public function index(): JsonResponse|SurveyQuestionsCollection
    {
        $service = SurveyService::active();
        if (is_null($service)) {
            return $this->errored('There no active survey');
        }

        return (new SurveyQuestionsCollection(
            $service->survey->questions()->with('answers')->get()
        ))->survey($service->survey);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $service = SurveyService::active();
        if (is_null($service)) {
            return $this->errored('There no active survey');
        }

        $response = $request->get('response');
        if (!is_array($response)) {
            throw ValidationException::withMessages(['response' => 'please enter response']);
        }

        $actor = SystemHelper::user();
        $ans = collect();
        $respondent = null;
        $date = now();
        foreach ($service->survey->questions()->with('answers')->get() as $question) {
            foreach ($response as $answer) {
                try {
                    if ($answer['id'] !== $question->SurveyQuestionId) {
                        continue;
                    }
                    $questionResponse = $answer['answers'][0]['value'];
                } catch (Exception $exception) {
                    throw ValidationException::withMessages([
                                                             $question->SurveyQuestionId => $question->SurveyQuestionId . ' does not have valid response.',
                                                            ]);
                }

                if (is_null($respondent)) {
                    $respondent = 'respondent ' . $question->responses()->count() + 1;
                }

                $txtResponse = null;
                $idResponse = null;
                if ($question->Type->value === SurveyQuestionTypeEnum::Open->value) {
                    $txtResponse = $questionResponse;
                } elseif ($question->Type->value === SurveyQuestionTypeEnum::Closed->value) {
                    $option = $question->answers()->where('Id', $questionResponse)->first();
                    if (!$option instanceof SurveyQuestionAnswer) {
                        throw ValidationException::withMessages([
                                                                 $question->SurveyQuestionId => $question->SurveyQuestionId . ' does not have valid response.',
                                                                ]);
                    }
                    $idResponse = $option->Id;
                } else {
                    throw ValidationException::withMessages([
                                                             $question->SurveyQuestionId => $question->SurveyQuestionId . ' has an error, contact support.',
                                                            ]);
                }

                $ans->add([
                           'Party'                  => $respondent,
                           'Source'                 => 'Website',
                           'Response'               => $txtResponse,
                           'SurveyQuestionAnswerID' => $idResponse,
                           'SurveyQuestionID'       => $question->Id,
                           'CreatedOn'              => $date,
                           'ModifiedOn'             => $date,
                           'CreatedBy'              => $actor->Id,
                           'ModifiedBy'             => $actor->Id,
                          ]);

                continue 2;
            }

            throw ValidationException::withMessages([
                                                     $question->SurveyQuestionId => $question->SurveyQuestionId . ' does not have valid response.',
                                                    ]);
        }

        DB::table('t_SurveyQuestionResponses')->insert($ans->toArray());

        return $this->succeeded('thank you for your response.');
    }
}
