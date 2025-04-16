<?php

namespace App\Http\Resources\Feedback;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SurveyQuestionsCollection extends ResourceCollection
{
    protected Survey $survey;

    public function survey(Survey $survey): static
    {
        $this->survey = $survey;
        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
                'survey'    => (new SurveyResource($this->survey)),
                'questions' => $this->collection->transform(function ($question) {
                    return [
                            'id'       => $question->SurveyQuestionId,
                            'type'     => $question->Type->name,
                            'question' => $question->Question,
                            'help'     => ($question->Notes) ?? '',
                            'answers'  => $this->getOptions($question),
                           ];
                }),
               ];
    }

    protected function getOptions(SurveyQuestion $question): array
    {
        $data = collect();
        if ($question->Type->value === SurveyQuestionTypeEnum::Closed->value) {
            $ops = $question->answers()->get();
            foreach ($ops as $op) {
                $data->add([
                            'id'     => $op->Id,
                            'option' => $op->Answer,
                            'help'   => ($op->Notes) ?? '',
                           ]);
            }
        }
        return $data->toArray();
    }
}
