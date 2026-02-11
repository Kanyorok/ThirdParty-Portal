<?php

namespace App\Services\Inventory;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemTypeService
{
    public function create(array $data): ItemType
    {
        $itemType = ItemType::create([
            'TypeName' => $data['TypeName'],
            'StockTracked' => $data['StockTracked'],
            'RequiresTagging' => $data['RequiresTagging'],
            'Active' => $data['Active'] ?? 0,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itemType)
            ->event('create')
            ->log('Created Item Type ' . $itemType->Id);

        return $itemType;
    }

    public function update(ItemType $itemType, array $data, bool $disableRelatedItems = false, bool $enableRelatedItems = false): ItemType
    {
        $user = Auth::user();

        DB::beginTransaction();

        try {
            $wasActive = $itemType->Active == 1;
            $wasInactive = $itemType->Active == 0;
            $willBeActive = isset($data['Active']) && $data['Active'] == 1;
            $willBeInactive = isset($data['Active']) && $data['Active'] == 0;

            $disabledCount = 0;
            $enabledCount = 0;

            if ($wasActive && $willBeInactive && $disableRelatedItems) {
                $disabledCount = $this->disableRelatedItems($itemType);
                Log::info("Disabled {$disabledCount} items related to Item Type {$itemType->Id}");
            }

            if ($wasInactive && $willBeActive && $enableRelatedItems) {
                $enabledCount = $this->enableRelatedItems($itemType);
                Log::info("Enabled {$enabledCount} items related to Item Type {$itemType->Id}");
            }

            $itemType->update([
                'TypeName' => $data['TypeName'],
                'StockTracked' => $data['StockTracked'],
                'RequiresTagging' => $data['RequiresTagging'],
                'Active' => $data['Active'] ?? 0,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);

            $logMessage = 'Updated Item Type ' . $itemType->Id;
            if ($disabledCount > 0) {
                $logMessage .= ' and disabled ' . $disabledCount . ' related items';
            }
            if ($enabledCount > 0) {
                $logMessage .= ' and enabled ' . $enabledCount . ' related items';
            }

            activity()
                ->causedBy($user)
                ->performedOn($itemType)
                ->event('update')
                ->log($logMessage);

            DB::commit();

            return $itemType;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating item type: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Check if item type has related items (active or inactive)
     *
     * @param ItemType $itemType
     * @param string $checkType 'active' or 'inactive'
     * @return array
     */
    public function checkRelatedItems(ItemType $itemType, string $checkType = 'active'): array
    {
        $query = ItemMasterList::where('ItemType', $itemType->Id)
            ->whereNull('DeletedOn');

        if ($checkType === 'active') {
            $query->whereHas('status', function ($q) {
                $q->where('Description', 'Active');
            });
        } else {
            $query->whereHas('status', function ($q) {
                $q->where('Description', 'Inactive');
            });
        }

        $relatedItems = $query->get();

        return [
            'hasItems' => $relatedItems->count() > 0,
            'count' => $relatedItems->count(),
            'items' => $relatedItems,
        ];
    }

    /**
     * Disable all active items related to an item type
     *
     * @param ItemType $itemType
     * @return int Number of items disabled
     */
    protected function disableRelatedItems(ItemType $itemType): int
    {
        $user = Auth::user();

        $inactiveStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Inactive')
            ->value('Id');

        if (! $inactiveStatusId) {
            Log::warning('Inactive status not found in CodeDetail table');

            throw new \Exception('Inactive status configuration not found');
        }

        $affectedRows = ItemMasterList::where('ItemType', $itemType->Id)
            ->whereNull('DeletedOn')
            ->whereHas('status', function ($query) {
                $query->where('Description', 'Active');
            })
            ->update([
                'Status' => $inactiveStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);

        activity()
            ->causedBy($user)
            ->performedOn($itemType)
            ->event('disable_related_items')
            ->log("Disabled {$affectedRows} items related to Item Type {$itemType->Id}");

        return $affectedRows;
    }

    /**
     * Enable all inactive items related to an item type
     *
     * @param ItemType $itemType
     * @return int Number of items enabled
     */
    protected function enableRelatedItems(ItemType $itemType): int
    {
        $user = Auth::user();

        $activeStatusId = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');

        if (! $activeStatusId) {
            Log::warning('Active status not found in CodeDetail table');

            throw new \Exception('Active status configuration not found');
        }

        $affectedRows = ItemMasterList::where('ItemType', $itemType->Id)
            ->whereNull('DeletedOn')
            ->whereHas('status', function ($query) {
                $query->where('Description', 'Inactive');
            })
            ->update([
                'Status' => $activeStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);

        activity()
            ->causedBy($user)
            ->performedOn($itemType)
            ->event('enable_related_items')
            ->log("Enabled {$affectedRows} items related to Item Type {$itemType->Id}");

        return $affectedRows;
    }

    public function delete(ItemType $itemType): void
    {
        $itemType->DeletedBy = Auth::id();
        $itemType->save();
        $itemType->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($itemType)
            ->event('delete')
            ->log('Deleted Item Type ' . $itemType->Id);
    }
}
