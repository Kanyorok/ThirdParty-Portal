<?php

namespace App\Services\FleetManagement;

use App\Models\FleetManagement\FleetMake;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetMakeService
{
    public function create(array $data): FleetMake
    {
        return DB::transaction(function () use ($data) {
            $data['BrandID'] = $this->generateBrandID();
            $data['BrandName'] = $data['BrandName'] ?? null;
            $data['Remarks'] = $data['Remarks'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            // Check if the fleet make already exists on soft deletes
            $existing = FleetMake::withTrashed()
                ->where('BrandName', $data['BrandName'])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();

                    return $existing;
                }

                throw new \Exception("The fleet brand already exists.");
            }


            return FleetMake::create($data);
        });
        activity()
            ->performedOn($make)
            ->causedBy(Auth::user())
            ->log('Fleet Make Created');
    }

    private function generateBrandID(): string
    {
        $latestMake = FleetMake::withTrashed()->latest('CreatedOn')->first();

        if (! $latestMake || ! $latestMake->BrandID) {
            return 'BRAND-0001';
        }

        $lastId = (int)str_replace('BRAND-', '', $latestMake->BrandID);
        $newId = $lastId + 1;

        return 'BRAND-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }

    public function update(FleetMake $make, array $data): FleetMake
    {
        return DB::transaction(function () use ($make, $data) {

            $make->update($data);
            $make->ModifiedBy = Auth::id();
            $make->ModifiedOn = now();
            $make->save();

            return $make;
        });
        activity()
            ->performedOn($make)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Fleet Make Updated');
    }

    public function delete(FleetMake $make): bool
    {
        return DB::transaction(function () use ($make) {

            $make->DeletedBy = Auth::id();
            $make->DeletedOn = now();
            $make->save();

            $make->delete();

            activity()
                ->performedOn($make)
                ->causedBy(Auth::user())
                ->log('Fleet Make Deleted');

            return true;
        });
    }
}
