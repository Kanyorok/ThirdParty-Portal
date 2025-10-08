<?php

namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategories extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemCategories';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        // return 'ItemCategoriesId';
        return 'Id';
    }

    protected $fillable = [
        'CategoryCode',
        'Name',
        'Description',
        'ParentId',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'CategoryCode'  => 'string',
        'Name'  => 'string',
        'Description'   => 'string',
        'ParentId'      => 'integer',
        'Status' => 'integer',
        'CreatedBy'     => 'integer',
        'ModifiedBy'    => 'integer',
        'DeletedBy'     => 'integer',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
    ];

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


    protected static function booted()
    {
        static::creating(function ($category) {
            // Generate top-level CategoryCode
            if (empty($category->CategoryCode) && empty($category->ParentId)) {
                $lastCategory = ItemCategories::whereNull('ParentId')
                    ->where('CategoryCode', 'like', 'CAT-%')
                    ->orderBy('Id', 'desc')
                    ->first();

                $lastCode = $lastCategory ? $lastCategory->CategoryCode : 'CAT-000';
                $lastNumber = (int)substr($lastCode, 4);
                $category->CategoryCode = 'CAT-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            // Generate sub-category CategoryCode
            if (empty($category->CategoryCode) && !empty($category->ParentId)) {
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
