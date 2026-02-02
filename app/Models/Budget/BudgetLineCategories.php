<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLineCategories extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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
