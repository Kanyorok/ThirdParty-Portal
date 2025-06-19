<?php

namespace App\Http\Requests\DMS;

use App\Models\DMS\Repository;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RepositoryRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'repository_name' => ['required', 'string', 'min:3', 'max:255'],
            'repository_parent' => ['required', 'string', 'max:255'],
            'repository_description' => ['nullable', 'string', 'min:3', 'max:255'],
        ];
    }

    public function getParentRepo(): Repository
    {
        $repo = Repository::query()->where('RepositoryId', $this->string('repository_parent')->trim())->first();
        if ($repo instanceof Repository) {//todo check permission for this repo.
            return $repo;
        }
        throw ValidationException::withMessages(['repository_parent' => 'invalid reo, refresh and try again']);
    }
}
