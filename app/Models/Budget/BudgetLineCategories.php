<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class BudgetLineCategories extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetLineCategories';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'CategoryCode',
        'CategoryName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineCategoriesId';
    }
}
