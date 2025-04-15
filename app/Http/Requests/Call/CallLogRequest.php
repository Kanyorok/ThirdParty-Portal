<?php

namespace App\Http\Requests\Call;

use App\Enums\CallStatusEnum;
use App\Enums\CallTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallLogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'phonenumber'    => [
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'AgentFirstName' => [
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'AgentExtension' => [
                                     'required',
                                     'integer',
                                    ],
                'ContactName'    => [
                                     'nullable',
                                     'string',
                                     'max:255',
                                    ],
                'StartTime'      => [
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'EndTime'        => [
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
                'CallType'       => [
                                     'required',
                                     'string',
                                     'max:20',
                                    ],
               ];
    }

    public function getStart(): Carbon
    {
        try {
            return Carbon::parse($this->validated('StartTime'));
        } catch (\Exception $e) {
        }

        return Carbon::now()->subMinutes();
    }

    public function getEnd(): Carbon
    {
        try {
            return Carbon::parse($this->validated('EndTime'));
        } catch (\Exception $e) {
        }

        return Carbon::now();
    }

    public function getCallType(): CallTypeEnum
    {
        if (Str::contains($this->validated('CallType'), ['Missed', 'Inbound'])) {
            return CallTypeEnum::Incoming;
        }

        // 'Outbound' //Notanswered
        return CallTypeEnum::Outgoing;
    }

    public function getCallStatus(): CallStatusEnum
    {
        //Notanswered -> outBund
        //  Missed -> Inbound Missed
        if (Str::contains($this->validated('CallType'), 'Inbound')) {
            if (Str::contains($this->validated('AgentExtension'), '800')) {
                return CallStatusEnum::NotReceived;
            }
            return CallStatusEnum::SuccessDiscussion;
        }

        if (Str::contains($this->validated('CallType'), 'Missed')) {
            return CallStatusEnum::NotReceived;
        }

        if (Str::contains($this->validated('CallType'), 'Notanswered')) {
            return CallStatusEnum::NotReceived;
        }
        //Outbound
        return CallStatusEnum::SuccessDiscussion;
    }
}
