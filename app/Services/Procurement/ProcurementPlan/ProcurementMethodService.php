<?php

namespace App\Services\Procurement\ProcurementPlan;

use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\ProcurementMethod;
use Illuminate\Support\Str;

class ProcurementMethodService
{
    /**
     * Create a new class instance.
     */

    public function create(array $data, User $actor, ConsolidatedProcurementPlan $consolidatedProcurementPlan, PlanLineItems $planLineItems): ProcurementMethod
    {
        $procurementMethod = ProcurementMethod::create([
            'MethodId' => 'Method-' . Str::upper(Str::random(5)),
            'ApprovedPlanId' => $consolidatedProcurementPlan->PlanID,
            'ApprovedPlanLineId' => $planLineItems->LineItemID,
            'AssignedMethod' => $data['AssignedMethod'],
            'Justification' => $data['Justification'],
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()
            ->causedBy($actor)
            ->performedOn($procurementMethod)
            ->event('create')
            ->log('Created Procurement Method ' . $procurementMethod->MethodId);

        return $procurementMethod;
    }
}
