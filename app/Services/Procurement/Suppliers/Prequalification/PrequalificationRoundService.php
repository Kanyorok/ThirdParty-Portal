<?php

namespace App\Services\Procurement\Suppliers\Prequalification;

use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use Illuminate\Database\Eloquent\Collection;

class PrequalificationRoundService
{
    public function GetAllForSupplier(int $SupplierId): Collection
    {
        return PrequalificationRound::query()
            ->select('t_PrequalificationRounds.*')
            ->addSelect([
                // Find application ID for the specific supplier
                'applicationId' => PrequalificationApplication::select('ApplicationID')
                    ->whereColumn('RoundID', 't_PrequalificationRounds.RoundID')
                    ->where('SupplierID', $SupplierId)
                    ->limit(1)
            ])
            ->where('Status', \App\Enums\Procurement\PrequalificationRoundEnum::Open)
            ->latest('CreatedOn')
            ->get();
    }

    public function GetById(int $Id): PrequalificationRound
    {
        return PrequalificationRound::findOrFail($Id);
    }

    public function Create(array $Data): PrequalificationRound
    {
        return PrequalificationRound::create($Data);
    }

    public function Update(int $Id, array $Data): PrequalificationRound
    {
        $Round = PrequalificationRound::findOrFail($Id);
        $Round->update($Data);
        return $Round;
    }

    public function Delete(int $Id): void
    {
        PrequalificationRound::findOrFail($Id)->delete();
    }
}
