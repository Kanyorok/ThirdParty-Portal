<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface SpecialPermissionContract
{
    /**
     * #permission -> role / permission name eg Read, Write ...
     */
    public function getShareEmailSubject(): string;

    public function getSharedName(): string;

    public function permissions(): MorphMany;
}
