<?php

namespace App\Http\Controllers\CRM\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\BoardMeetingRequest;
use App\Traits\Controller\ScheduleTrait;
use Illuminate\Http\JsonResponse;

class ScheduleActionController extends Controller
{
    use ScheduleTrait;

    public function __construct()
    {
        $this->middleware('ajax');
    }


    public function board(BoardMeetingRequest $request): JsonResponse
    {
        return $this->succeeded('deprecated use the other one');
    }
}
