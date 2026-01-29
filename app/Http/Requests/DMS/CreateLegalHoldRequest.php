<?php

namespace App\Http\Requests\DMS;

use App\Models\DMS\DMSTags;
use App\Models\DMS\LegalHold;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateLegalHoldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Ref' => ['nullable', 'string', 'max:200'],
            'Name' => ['required', 'string', 'min:3', 'max:200'],
            'Tags' => ['required', 'min:1', 'array'],
        ];
    }

    public function getDocumentIds(): array
    {
        $documentIDs = DMSTags::query()->whereIn('TagID', $this->array('Tags', []))
            ->with('documents')
            ->get()
            ->pluck('documents')
            ->flatten()
            ->pluck('Id')
            ->unique();
        if ($documentIDs->isEmpty()) {
            throw ValidationException::withMessages(['Tags' => 'No valid tags, must have documents associated with them.']);
        }

        return $documentIDs->toArray();
    }

    public function getRef(): string
    {
        $ref = $this->string('Ref', '')->trim()->toString();
        if ($ref === '') {
            $number = LegalHold::withTrashed()->count();
            do {
                $number++;
                $ref = Str::slug('Hold' . Str::padLeft(($number), 4, '0'));
            } while (LegalHold::where('Ref', $ref)->withTrashed()->exists());
        }

        return $ref;
    }
}
