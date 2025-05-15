<?php

namespace App\Http\Requests\Board;

use App\Models\HRM\Committee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommitteeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'CommitteeName'  => [
                                     'required',
                                     'string',
                                     'max:200',
                                     Rule::unique('t_Committees', 'Name')->ignore($this->route('committee')),
                                    ],
                'CommitteeNotes' => [
                                     'nullable',
                                     'string',
                                     'max:2000',
                                    ],
               ];
    }

    public function generateID(): string
    {
        $number = Committee::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::upper(Str::slug('Comm-' . Str::padLeft(($number), 3, '0')));
        } while (Committee::where('CommitteeID', $slug)->withTrashed()->exists());

        return $slug;
    }
}
