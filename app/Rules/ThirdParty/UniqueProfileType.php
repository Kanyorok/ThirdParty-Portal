<?php

namespace App\Rules\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\ThirdParty\ThirdPartyUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueProfileType implements ValidationRule
{
    public function __construct(
        protected ThirdPartyUser $user,
        protected ThirdPartyTypeEnum $profileType
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->user->hasProfile()) {
            return;
        }

        $thirdParty = $this->user->thirdParty;
        if (!$thirdParty) {
            return;
        }

        $hasProfileType = $thirdParty->types()
            ->where('Code', 'like', $this->profileType->getCode() . '%')
            ->exists();

        if ($hasProfileType) {
            $fail("You already have a {$this->profileType->label()} profile.");
        }
    }
}
