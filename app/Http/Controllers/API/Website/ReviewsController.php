<?php

namespace App\Http\Controllers\API\Website;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\ReviewRequest;
use App\Services\Feedback\ReviewService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReviewsController extends Controller
{
    /**
     * Handle the incoming request.
     * @throws ValidationException
     */
    public function __invoke(ReviewRequest $request): JsonResponse
    {
        $name = $request->getName();
        $actor = SystemHelper::user();
        try {
            DB::transaction(static function () use ($name, $actor, $request) {
                ReviewService::anonymous($name, $request->getRate(), $request->getReview(), 'Website', $actor);
            });
        } catch (Exception $e) {
            Log::error('Error saving review from website : ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('thank you for your feedback');
    }
}
