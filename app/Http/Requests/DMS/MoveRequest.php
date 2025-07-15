<?php

namespace App\Http\Requests\DMS;

use App\Models\Auth\User;
use App\Models\DMS\Repository;
use App\Services\DMS\RepositoryService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MoveRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Repository' => 'required',
        ];
    }

    public function getRepository(User $actor): Repository
    {
        $repo = RepositoryService::getUserQuery($actor, [$this->validated('Repository')])->first();
        if (!$repo instanceof Repository) {
            return $repo;
        }
        throw ValidationException::withMessages([
            'Repository' => __('dms.repository.not_found'),
        ]);
    }
}
