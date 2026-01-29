<?php

namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;

class ItemSubCategories extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
                           'Status',
                          ];

    protected $casts = [
                            'SubCategoryCode' => 'string',
                            'SubCategoryName' => 'string',
                            'ParentCategory' => 'string',
                            'Description' => 'string',
                            'Status' => 'boolean',

    ];

    public function parentCategory()
    {
        return $this->belongsTo(ItemCategories::class, 'ParentCategory', 'id');
    }
}
