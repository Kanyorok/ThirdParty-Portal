<?php

namespace App\Services\Procurement\Suppliers\Prequalification;

use App\Models\Procurement\Prequalification\PrequalificationRound;
use Illuminate\Database\Eloquent\Collection;

class PrequalificationRoundService
{
    public function GetAll(): Collection
    {
        return PrequalificationRound::all();
    }

    public function Create(array $Data): PrequalificationRound
    {
        return PrequalificationRound::create($Data);
    }

    public function GetById(int $Id): PrequalificationRound
    {
        return PrequalificationRound::findOrFail($Id);
    }

    public function Update(int $Id, array $Data): PrequalificationRound
    {
        $Round = PrequalificationRound::findOrFail($Id);
        $Round->update($Data);
        return $Round;
    }

    public function Delete(int $Id): void
    {
        $Round = PrequalificationRound::findOrFail($Id);
        $Round->delete();
    }
}
