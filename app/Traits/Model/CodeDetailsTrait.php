<?php

namespace App\Traits\Model;

use App\Models\Core\Approval\CodeDetail;
use Illuminate\Validation\ValidationException;

trait CodeDetailsTrait
{
    protected function getCodeDetail(string $codeId, string $inputKey): CodeDetail
    {
        $value = $this->validated($inputKey);

        if (!$value) {
            throw ValidationException::withMessages([
                $inputKey => "The {$inputKey} field is required."
            ]);
        }

        $detail = CodeDetail::query()
            ->where('CodeID', $codeId)
            ->where('Value', $value)
            ->where('IsActive', 1)
            ->first();

        if ($detail instanceof CodeDetail) {
            return $detail;
        }

        throw ValidationException::withMessages([
            $inputKey => "{$value} is not a valid {$codeId}."
        ]);
    }
}
