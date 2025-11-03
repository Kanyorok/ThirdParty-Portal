<?php

namespace App\Services;

use App\Models\Auth\User;
use App\Models\Settings\WorkFlowStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Settings\WorkFlow;

class WorkFlowStageService
{
    public function createStage(array $data): WorkFlowStage
    {
        /** @var User $user */
        $user = Auth::user();

        $nextOrder = WorkFlowStage::where('WorkFlowId', $data['WorkFlowId'])->max('Order') + 1;

        $stage = WorkFlowStage::create([
            'StageName' => $data['StageName'],
            'EscalationLimit' => $data['EscalationLimit'],
            'WorkFlowId' => $data['WorkFlowId'],
            'WorkFlowTypeId' => $data['WorkFlowTypeId'],
            'WorkFlowLimitId' => $data['WorkFlowLimitId'] ?? null,
            'PermissionId' => $data['PermissionId'],
            'Count' => $data['Count'],
            'Order' => $nextOrder,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);

        if (!empty($data['IsFinalStage']) && $data['IsFinalStage'] == 1) {
            WorkFlow::where('Id', $data['WorkFlowId'])->update(['IsFinalStage' => true]);
        }

        activity()->causedBy($user)
            ->performedOn($stage)
            ->event('create')
            ->log('Created approval workflow stage: ' . $data['StageName']);

        return $stage;
    }

    public function deleteStage(int $id): bool
    {
        $stage = WorkFlowStage::findOrFail($id);
        $stage->delete();

        activity()->performedOn($stage)
            ->event('delete')
            ->log('Deleted approval workflow stage: ' . $stage->StageName);

        return true;
    }

    public function updateStage(int $id, array $data): WorkFlowStage
    {
        $stage = WorkFlowStage::findOrFail($id);
        $stage->update($data);

        activity()->performedOn($stage)
            ->event('update')
            ->log('Updated approval workflow stage: ' . $stage->StageName);

        return $stage;
    }

    public function getStageById(int $id): WorkFlowStage
    {
        return WorkFlowStage::findOrFail($id);
    }
}
