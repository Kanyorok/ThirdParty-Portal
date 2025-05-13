<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;

class ItemSubCategory extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemSubCategory';
    protected $primaryKey = 'Id';

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
                            'ParentCategory'      => 'string',
                            'Description'      => 'string',
                            'Status'      => 'string',

                        ];   

    public function parentCategory()
    {
        return $this->belongsTo(ItemCategories::class, 'ParentCategory', 'id');
    }
}      
