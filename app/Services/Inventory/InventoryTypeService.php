<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemMasterList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryTypeService
{
    public function create(array $data): InventoryType
    {
        $user = Auth::user();

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = Carbon::now();
        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        $type = InventoryType::create($data);

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('create')
            ->log('Created Inventory Type ' . $type->Id);

        return $type;
    }

    public function update(InventoryType $type, array $data, bool $disableRelatedItems = false, bool $enableRelatedItems = false): InventoryType
    {
        $user = Auth::user();

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = Carbon::now();

        DB::beginTransaction();
        try {
            $wasActive = $type->Status == 1;
            $wasInactive = $type->Status == 0;
            $willBeActive = isset($data['Status']) && $data['Status'] == 1;
            $willBeInactive = isset($data['Status']) && $data['Status'] == 0;

            $disabledCount = 0;
            $enabledCount = 0;

            if ($wasActive && $willBeInactive && $disableRelatedItems) {
                $disabledCount = $this->disableRelatedItems($type);
                Log::info("Disabled {$disabledCount} items related to Inventory Type {$type->Id}");
            }

            if ($wasInactive && $willBeActive && $enableRelatedItems) {
                $enabledCount = $this->enableRelatedItems($type);
                Log::info("Enabled {$enabledCount} items related to Inventory Type {$type->Id}");
            }

            $type->update($data);

            $logMessage = 'Updated Inventory Type ' . $type->Id;
            if ($disabledCount > 0) {
                $logMessage .= ' and disabled ' . $disabledCount . ' related items';
            }
            if ($enabledCount > 0) {
                $logMessage .= ' and enabled ' . $enabledCount . ' related items';
            }

            activity()
                ->causedBy($user)
                ->performedOn($type)
                ->event('update')
                ->log($logMessage);

            DB::commit();
            return $type;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating inventory type: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if inventory type has related items (active or inactive)
     * 
     * @param InventoryType $type
     * @param string $checkType 'active' or 'inactive'
     * @return array
     */
    public function checkRelatedItems(InventoryType $type, string $checkType = 'active'): array
    {
        $query = ItemMasterList::where('InventoryType', $type->Id)
            ->whereNull('DeletedOn');

        if ($checkType === 'active') {
            $query->whereHas('status', function($q) {
                $q->where('Description', 'Active');
            });
        } else {
            $query->whereHas('status', function($q) {
                $q->where('Description', 'Inactive');
            });
        }

        $relatedItems = $query->get();

        return [
            'hasItems' => $relatedItems->count() > 0,
            'count' => $relatedItems->count(),
            'items' => $relatedItems
        ];
    }

    /**
     * Disable all active items related to an inventory type
     * 
     * @param InventoryType $type
     * @return int Number of items disabled
     */
    protected function disableRelatedItems(InventoryType $type): int
    {
        $user = Auth::user();
        
        $inactiveStatusId = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Inactive')
            ->value('Id');

        if (!$inactiveStatusId) {
            Log::warning('Inactive status not found in CodeDetail table');
            throw new \Exception('Inactive status configuration not found');
        }

        $affectedRows = ItemMasterList::where('InventoryType', $type->Id)
            ->whereNull('DeletedOn')
            ->whereHas('status', function($query) {
                $query->where('Description', 'Active');
            })
            ->update([
                'Status' => $inactiveStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now()
            ]);

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('disable_related_items')
            ->log("Disabled {$affectedRows} items related to Inventory Type {$type->Id}");

        return $affectedRows;
    }

    /**
     * Enable all inactive items related to an inventory type
     * 
     * @param InventoryType $type
     * @return int Number of items enabled
     */
    protected function enableRelatedItems(InventoryType $type): int
    {
        $user = Auth::user();
        
        $activeStatusId = \App\Models\Core\Approval\CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');

        if (!$activeStatusId) {
            Log::warning('Active status not found in CodeDetail table');
            throw new \Exception('Active status configuration not found');
        }

        $affectedRows = ItemMasterList::where('InventoryType', $type->Id)
            ->whereNull('DeletedOn')
            ->whereHas('status', function($query) {
                $query->where('Description', 'Inactive');
            })
            ->update([
                'Status' => $activeStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now()
            ]);

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('enable_related_items')
            ->log("Enabled {$affectedRows} items related to Inventory Type {$type->Id}");

        return $affectedRows;
    }

    public function delete(InventoryType $type): void
    {
        $user = Auth::user();

        $type->DeletedBy = Auth::id();
        $type->save();
        $type->delete();

        activity()
            ->causedBy($user)
            ->performedOn($type)
            ->event('delete')
            ->log('Deleted Inventory Type ' . $type->Id);
    }
}