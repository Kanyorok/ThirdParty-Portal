<?php

namespace App\Services\FleetManagement;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\FleetManagement\FleetModel;
use App\Models\FleetManagement\FleetMake;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\User;
use App\Http\Requests\FleetManagement\FleetModelRequest;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class FleetModelService
{

    public function create(array $data): FleetModel
    {
        return DB::transaction(function () use ($data) {
            $data['ModelID'] = $this->generateModelID();
            $data['ModelName'] = $data['ModelName'] ?? null;
            $data['BrandID'] = $data['BrandID'] ?? null;
            $data['Remarks'] = $data['Remarks'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            // Check if the fleet model already exists on soft deletes
            $existing = FleetModel::withTrashed()
                ->where('ModelName', $data['ModelName'])
                ->where('BrandID', $data['BrandID'])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    return $existing;
                }
                throw new \Exception("This model already exists for the selected brand.");
            }

            return FleetModel::create($data);

        });
        activity()
            ->performedOn($model)
            ->causedBy(Auth::user())
            ->log('Fleet Model Created');
    }

    private function generateModelID(): string
    {
        $latestModel = FleetModel::withTrashed()->latest('CreatedOn')->first();

        if (!$latestModel || !$latestModel->ModelID) {
            return 'MOD-0001';
        }

        $lastId = (int)str_replace('MOD-', '', $latestModel->ModelID);
        $newId = $lastId + 1;

        return 'MOD-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }


    public function update(FleetModel $model, array $data): FleetModel
    {
        return DB::transaction(function () use ($model, $data) {

            $model->update($data);
            $model->ModifiedBy = Auth::id();
            $model->ModifiedOn = now();
            $model->save();
            return $model;
        });
        activity()
            ->performedOn($model)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Fleet Model Updated');
    }

    public function delete(FleetModel $model): bool
    {
        return DB::transaction(function () use ($model) {

            $model->DeletedBy = Auth::id();
            $model->DeletedOn = now();
            $model->save();

            $model->delete();

            activity()
                ->performedOn($model)
                ->causedBy(Auth::user())
                ->log('Fleet Model Deleted');

            return true;
        });
    }
}


