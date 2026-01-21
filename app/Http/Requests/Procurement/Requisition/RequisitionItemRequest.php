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
            'Type' => ['nullable', 'string'],
            'Item' => ['required', 'integer'],
            'Quantity' => ['required', 'numeric', 'min:1'],
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
                
                \Illuminate\Support\Facades\Log::info('RequisitionItemRequest Validation', [
                    'requisitionId' => $requisitionId,
                    'itemId' => $itemId,
                    'requestedQty' => $requestedQty,
                    'planId' => $planId
                ]);

                if ($planId) {
                    // Fetch plan line item details
                    $planLineItem = DB::table('t_PlanLineItem')
                        ->where('PlanID', $planId)
                        ->where('ItemID', $itemId)
                        ->where('IsDeleted', 0)
                        ->select('LineItemID', 'MergedQty', 'OriginalQTY')
                        ->first();

                    \Illuminate\Support\Facades\Log::info('Plan Line Item Found', ['planLineItem' => $planLineItem]);

                    if ($planLineItem) {
                        $totalPlanQty = $planLineItem->MergedQty; // Use MergedQty as the main quantity

                        // Sum of already requisitioned quantities for this plan line item
                        $alreadyUsedQty = DB::table('t_RequisitionLines')
                            ->where('PlanLineRef', $planLineItem->LineItemID)
                            ->whereNull('DeletedOn')
                            ->sum('Quantity');

                        $remainingQty = $totalPlanQty - $alreadyUsedQty;
                        
                        \Illuminate\Support\Facades\Log::info('Quantity Calculation', [
                            'totalPlanQty' => $totalPlanQty,
                            'alreadyUsedQty' => $alreadyUsedQty,
                            'remainingQty' => $remainingQty,
                            'requestedQty' => $requestedQty
                        ]);

                        if ($requestedQty > $remainingQty) {
                            $v->errors()->add('Quantity', "Requested quantity ($requestedQty) exceeds remaining quantity ($remainingQty) from the plan.");
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Plan Line Item NOT Found', ['PlanID' => $planId, 'ItemID' => $itemId]);
                    }
                }
            }
        });
    }
}
