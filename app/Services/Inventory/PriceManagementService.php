<?php

namespace App\Services\Inventory;

use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PriceManagementService
{
    public function validate(array $data)
    {
        return Validator::make($data, [
            'ItemID' => 'required|integer|exists:t_Items,Id',
            'UOM' => 'required|integer|exists:t_UOM,Id',
            'EstimatedPrice' => 'required|numeric',
            'ActualPrice' => 'required|numeric',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after:EffectiveFrom',
            'IsDefault' => 'nullable|boolean',
            'Source' => 'nullable|string|max:255',
        ]);
    }

    public function create(array $data): PriceManagement
    {
        $validator = $this->validate($data);
        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }

        $pricing = new PriceManagement($data);
        $pricing->CreatedBy = Auth::id();
        $pricing->CreatedOn = Carbon::now();
        $pricing->ModifiedBy = Auth::id();
        $pricing->ModifiedOn = Carbon::now();
        $pricing->save();

        $pricing->PriceID = 'PR-' . str_pad($pricing->Id, 5, '0', STR_PAD_LEFT);
        $pricing->save();

        
        $item = ItemMasterList::find($pricing->ItemID);
        if ($item) {
            $item->ItemPrice = $pricing->Id;
            $item->save();
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Created Item Pricing');

        return $pricing;
    }

    public function update(PriceManagement $pricing, array $data): PriceManagement
    {
        $validator = $this->validate($data);
        if ($validator->fails()) {
            abort(422, $validator->errors()->first());
        }

        $pricing->fill($data);
        $pricing->ModifiedBy = Auth::id();
        $pricing->ModifiedOn = Carbon::now();
        $pricing->save();

        
        $item = ItemMasterList::find($pricing->ItemID);
        if ($item && $item->ItemPrice != $pricing->Id) {
            $item->ItemPrice = $pricing->Id;
            $item->save();
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Updated Item Pricing');

        return $pricing;
    }

    public function delete(PriceManagement $pricing): bool
    {
        $pricing->DeletedBy = Auth::id();
        $pricing->save();
        $pricing->delete();

        $item = \App\Models\Inventory\ItemMasterList::where('ItemPrice', $pricing->Id)->first();
        if ($item) {
            $item->ItemPrice = null;
            $item->save();
        }

        activity()
            ->performedOn($pricing)
            ->causedBy(Auth::user())
            ->log('Deleted Item Pricing');

        return true;
    }

    public function list($filters = [])
    {
        $query = PriceManagement::with(['item', 'uom']);
     
        return $query->orderBy('CreatedOn', 'asc')->get();
    }
}