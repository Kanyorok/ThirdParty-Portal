<?php

namespace App\Http\Controllers\CRM\Base;

use App\Http\Controllers\Controller;
use App\Models\Communication\SMS;
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
        $sms = SMS::query()->where('SMSId', $sms_id)->first();
        if (!$sms instanceof SMS) {
            return $this->errored('could not load sms');
        }

        return view('crm.base.sms.summary', compact('sms'))
            ->with('party', $sms->party);
    }
}
