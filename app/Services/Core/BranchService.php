<?php

namespace App\Services\Core;

use App\Models\Core\Branch;

class BranchService
{
    public function __construct(public Branch $branch)
    {
    }

    public static function hqBranches(): array
    {
        return Branch::where('IsHQ', true)->get()->pluck('name', 'id')->toArray();
    }

    public function isHQ(): bool
    {
        return (bool)$this->branch->IsHQ;
    }
}
