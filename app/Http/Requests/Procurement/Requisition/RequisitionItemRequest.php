<?php

namespace App\Http\Requests\Procurement\Requisition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class RequisitionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'RequisitionID' => ['required', 'integer'],
            'Type' => ['required', 'string'],
            'Item' => ['required', 'integer'],
            'Quantity' => ['required', 'numeric'],
            'Urgency' => ['required', 'integer'],
            'UOM' => ['required', 'integer'],
            'EstimatedPrice' => ['nullable', 'numeric'],
            'LineItemID' => ['nullable', 'integer'],
        ];
    }

    /**
     * Add custom validation after default rules pass.
     */
    public function withValidator($validator)
    {
        $validator->after(function (Validator $v) {
            $requisitionId = $this->input('RequisitionID');
            $itemId = $this->input('Item');
            $requestedQty = $this->input('Quantity');

            if ($requisitionId && $itemId && $requestedQty) {
                // Fetch PlanRef from requisition
                $planId = DB::table('t_Requisitions')->where('Id', $requisitionId)->value('PlanRef');

                if ($planId) {
                    // Fetch original plan quantity
                    $originalQty = DB::table('t_PlanLineItem')
                        ->where('PlanID', $planId)
                        ->where('ItemID', $itemId)
                        ->value('OriginalQty');

                    // Sum of already requisitioned quantities for this item and plan
                    $alreadyUsedQty = DB::table('t_RequisitionLines as rl')
                        ->join('t_Requisitions as r', 'rl.RequisitionID', '=', 'r.Id')
                        ->where('r.PlanRef', $planId)
                        ->where('rl.Item', $itemId)
                        ->sum('rl.Quantity');

                    $remainingQty = $originalQty - $alreadyUsedQty;

                    if ($requestedQty > $remainingQty) {
                        $v->errors()->add('Quantity', "Requested quantity ($requestedQty) exceeds remaining quantity ($remainingQty) from the plan.");
                    }
                }
            }
        });
    }
}
