<?php

namespace App\Http\Requests\Board;

use App\Models\Committee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkNotificationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'NotificationLabel'     => [
                                            'required',
                                            'string',
                                            'max:255',
                                           ],
                'NotificationCommittee' => [
                                            'required',
                                            Rule::exists('t_Committees', 'CommitteeID'),
                                           ],
                'NotificationContent'   => [
                                            'required',
                                            'string',
                                            'max:2000',
                                           ],
               ];
    }

    public function getCommittee(): Committee
    {
        $committee = Committee::query()->where('CommitteeID', $this->validated('NotificationCommittee'))->first();
        if ($committee instanceof Committee) {
            if ($committee->members()->count() === 0) {
                throw ValidationException::withMessages(['NotificationCommittee' => 'committee has no members']);
            }
            return $committee;
        }

        throw ValidationException::withMessages(['NotificationCommittee' => 'invalid committee']);
    }
}
