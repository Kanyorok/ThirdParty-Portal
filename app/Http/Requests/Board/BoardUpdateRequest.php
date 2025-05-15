<?php

namespace App\Http\Requests\Board;

use App\Models\HRM\Committee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'BoardMemberPhone'  => [
                                        'nullable',
                                        'string',
                                        'max:200',
                                       ],
                'BoardMemberEmail'  => [
                                        'required',
                                        'email:dns',
                                        'max:200',
                                       ],
                'BoardCommittees'   => [
                                        'required',
                                        'array',
                                        'min:1',
                                        'max:20',
                                       ],
                'BoardCommittees.*' => [
                                        'required',
                                        Rule::exists('t_Committees', 'CommitteeID'),
                                       ],
                'BoardMemberRole'   => [
                                        'nullable',
                                        'string',
                                        'max:200',
                                       ],
                'BoardMemberNotes'  => [
                                        'nullable',
                                        'string',
                                        'max:2000',
                                       ],
               ];
    }

    public function getCommittees(): array
    {
        return Committee::query()->whereIn('CommitteeID', $this->validated('BoardCommittees'))->select('Id')->pluck('Id')->toArray();
    }
}
