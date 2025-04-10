<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\CrmSMS;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SMSController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax');
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $sms_id): JsonResponse|View
    {
        $sms = CrmSMS::query()->where('SMSId', $sms_id)->first();
        if (!$sms instanceof CrmSMS) {
            return $this->errored('could not load sms');
        }

        return view('base.sms.summary', compact('sms'))
            ->with('party', $sms->party);
    }
}
