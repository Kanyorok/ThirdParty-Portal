<?php

namespace App\Http\Controllers\CRM\Marketing\Competitor;

use App\Events\Marketing\CompetitorRoachEvent;
use App\Http\Controllers\Controller;
use App\Models\Competitor;
use App\Services\ThirdParty\AIService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompetitorLLMController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Progress bar.
     */
    public function index(Competitor $competitor): JsonResponse
    {
        $this->authorize('view', $competitor);
        if (is_array($competitor->Processing) && array_key_exists('done', $competitor->Processing) && array_key_exists('total', $competitor->Processing)) {
            $total = $competitor->Processing['total'];
            $done = $competitor->Processing['done'];
        } else {
            $total = 0;
            $done = 0;
        }

        return $this->succeeded('ok', data: [
            'progress' => (int)($total > 0) ? (($done / $total) * 100) : 100,
            'done' => $done,
            'total' => (int)$total,
            'description' => 'Fetching & Processing Data (' . number_format($done) . ' / ' . number_format($total) . ')'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Competitor $competitor): JsonResponse
    {
        $this->authorize('llm', $competitor);

        if (!AIService::hasValid()) {
            return $this->errored('No valid LLM (AI) Credentials defined.');
        }

        if (!is_null($competitor->Processing)) {
            return $this->errored('processing already started');
        }

        try {
            if (!filter_var($competitor->Website, FILTER_VALIDATE_URL) || !Http::get($competitor->Website)->successful()) {
                return $this->errored('Invalid Website URL');
            }
        } catch (\Exception|\Throwable) {
            return $this->errored('Invalid Website URL');
        }

        try {
            DB::transaction(static function () use ($competitor, $request) {
                $competitor->update(['Processing' => ['done' => 1, 'total' => 10]]);
                event(new CompetitorRoachEvent($competitor, ($request->clear === 'yes')));
                activity()->causedBy($request->user())->performedOn($competitor)->event('crawl')->log('Start LLM competitor (' . $competitor->CompetitorID . ') data fetching and processing.');
            });
        } catch (Exception|\Throwable $e) {
            Log::error('Start LLM Competitor data fetching and processing failed: .');
            Log::error($e);
            return $this->errored('an unexpected error occurred');
        }
        return $this->succeeded('processing started', route('competitors.show', $competitor->CompetitorID));
    }
}
