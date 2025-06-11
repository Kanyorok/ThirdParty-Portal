<?php

namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;

class ItemSubCategories extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemSubCategories';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ItemSubCategoriesId';
    }

    protected $fillable = [
                           'SubCategoryCode',
                           'SubCategoryName',
                           'ParentCategory',
                           'Description',
                           'Status'
                          ];

    protected $casts = [
                            'SubCategoryCode'       => 'string',
                            'SubCategoryName'       => 'string',
                            'ParentCategory'     => 'string',
                            'Description'      => 'string',
                            'Status'      => 'boolean',

    ];

    public function parentCategory()
    {
        return $this->belongsTo(ItemCategories::class, 'ParentCategory', 'id');
    }
}
