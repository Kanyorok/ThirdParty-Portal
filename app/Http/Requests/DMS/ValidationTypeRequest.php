<?php

namespace App\Http\Requests\DMS;

use App\Http\Requests\Core\ShareRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class ValidationTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Name' => 'required|string|max:255',
            'Notes' => 'nullable|string|max:5000',
            'Approvers' => 'required|array|min:1',
        ];
    }

    public function getApprovers(): Collection
    {
        $approvers = $this->array('Approvers');
        $Actors = collect();
        if (!is_array($approvers)) {
            return $Actors;
        }
        foreach ($approvers as $approver) {
            try {
                $actor = (new ShareRequest())->getAssignee($approver, 'Approvers');
            } catch (\Exception) {
                continue;
            }
            $Actors->add($actor);
        }
        return $Actors;
    }
}
