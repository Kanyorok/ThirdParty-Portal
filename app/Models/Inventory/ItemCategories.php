<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategories extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemCategories';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ItemCategoriesId';
    }

    protected $fillable = [
        'CategoryCode',
        'Name',
        'Description',
        'ParentId',
        'Status',
        'ItemTypeId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'CategoryCode' => 'string',
        'Name' => 'string',
        'Description' => 'string',
        'ParentId' => 'integer',
        'Status' => 'integer',
        'ItemTypeId' => 'integer',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'ItemTypeId', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function items()
    {
        return $this->hasMany(ItemMasterList::class, 'Category', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function parent()
    {
        return $this->belongsTo(ItemCategories::class, 'ParentId');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'ParentId');
    }

    public function inUse(): bool
    {
        return $this->children()->exists() || $this->items()->exists();
    }

    public function hasItems(): bool
    {
        return $this->items()->exists();
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->CategoryCode) && empty($category->ParentId)) {
                $lastCategory = ItemCategories::whereNull('ParentId')
                    ->where('CategoryCode', 'like', 'CAT-%')
                    ->orderBy('Id', 'desc')
                    ->first();

                $lastCode = $lastCategory ? $lastCategory->CategoryCode : 'CAT-000';
                $lastNumber = (int)substr($lastCode, 4);
                $category->CategoryCode = 'CAT-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            if (empty($category->CategoryCode) && ! empty($category->ParentId)) {
                $parentCategory = ItemCategories::find($category->ParentId);

                $lastSubCategory = ItemCategories::where('ParentId', $category->ParentId)
                    ->where('CategoryCode', 'like', 'SUB-%')
                    ->orderBy('Id', 'desc')
                    ->first();

                $lastSubCode = $lastSubCategory ? $lastSubCategory->CategoryCode : 'SUB-000';
                $lastSubNumber = (int)substr($lastSubCode, 4);
                $category->CategoryCode = 'SUB-' . str_pad($lastSubNumber + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
