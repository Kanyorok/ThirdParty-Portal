<?php

namespace App\Http\Controllers\CRM\Feedback;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\CRM\Survey;
use App\Services\Feedback\SurveyService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurveyApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('approve', $survey);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($survey, $actor) {
                (new SurveyService($survey))->workflowApprove($actor);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error approve survey failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey approved successfully.', route('surveys.index'));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('approve', $survey);
        $actor = $request->user();
        $data = $request->validate([
                                    'survey_reject_reason' => [
                                                               'required',
                                                               'string',
                                                               'min:15',
                                                               'max:2000',
                                                              ],
                                   ]);

        try {
            DB::transaction(static function () use ($survey, $actor, $data) {
                (new SurveyService($survey))->workflowReject($actor, $data['survey_reject_reason']);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error reject survey failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('survey rejected successfully.', route('surveys.index'));
    }
}
