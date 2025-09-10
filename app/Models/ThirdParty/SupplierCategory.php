<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ThirdParies\Supplier;
use App\Models\Inventory\ItemCategories; // model representing t_ItemCategories
use Illuminate\Support\Facades\DB;

class SupplierCategory extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierCategories';
    protected $primaryKey = 'SupplierCategoryID';

    protected $fillable = [
        'CategoryName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'IsActive' => 'boolean',
    ];

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(
            Supplier::class,
            't_ThirdParty_SupplierCategory',
            'SupplierCategoryID',
            'ThirdPartyID'
        )->withTimestamps();
    }

    public function itemCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemCategories::class,
            't_SupplierCategory_ItemCategory',
            'SupplierCategoryID',
            'ItemCategoryID'
        )
            ->withPivot([
                'CreatedBy',
                'CreatedOn',
                'ModifiedBy',
                'ModifiedOn',
                'DeletedBy',
                'DeletedOn'
            ])
            ->whereNull('t_SupplierCategory_ItemCategory.DeletedOn');
    }

    /**
     * Sync item categories with audit tracking on the pivot.
     * - Newly attached rows get CreatedBy/CreatedOn
     * - Existing (non-deleted) rows updated get ModifiedBy/ModifiedOn
     * - Missing rows are soft deleted (DeletedBy/DeletedOn)
     */
    public function syncItemCategoriesWithAudit(array $newIds, int $userId): void
    {
        $newIds = collect($newIds)->filter()->unique()->values();

        $pivotTable = 't_SupplierCategory_ItemCategory';
        $now = now();

        // Current active ids
        $current = DB::table($pivotTable)
            ->where('SupplierCategoryID', $this->getKey())
            ->whereNull('DeletedOn')
            ->pluck('ItemCategoryID');

        $toAdd = $newIds->diff($current);
        $toKeep = $newIds->intersect($current);
        $toRemove = $current->diff($newIds); // soft delete

        if ($toAdd->isNotEmpty()) {
            $insertRows = $toAdd->map(fn($id) => [
                'SupplierCategoryID' => $this->getKey(),
                'ItemCategoryID' => $id,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
            ])->all();
            DB::table($pivotTable)->insert($insertRows);
        }

        if ($toKeep->isNotEmpty()) {
            // Update ModifiedBy / ModifiedOn for kept rows
            DB::table($pivotTable)
                ->where('SupplierCategoryID', $this->getKey())
                ->whereIn('ItemCategoryID', $toKeep)
                ->whereNull('DeletedOn')
                ->update([
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                ]);
        }

        if ($toRemove->isNotEmpty()) {
            DB::table($pivotTable)
                ->where('SupplierCategoryID', $this->getKey())
                ->whereIn('ItemCategoryID', $toRemove)
                ->whereNull('DeletedOn')
                ->update([
                    'DeletedBy' => $userId,
                    'DeletedOn' => $now,
                ]);
        }
    }
}
