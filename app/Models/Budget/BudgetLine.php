<?php

namespace App\Models\Budget;

use App\Models\Core\Approval\CodeDetail;
use App\Models\HRM\Department;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLine extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_BudgetLines';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetLineId';
    }

    protected $fillable = [
        'BudgetLineCategoryID',
        'LineName',
        'DepartmentID',
        'GLAccountTypeID',
        'GLAccountSubTypeID',
        'Description',
        'IsDefault',
        'IsProductDriven',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'IsDefault' => 'boolean',
        'IsProductDriven' => 'boolean',

        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    //relations

    public function category()
    {
        return $this->belongsTo(BudgetLineCategories::class, 'BudgetLineCategoryID', 'Id');
    }

    public function glAccounts()
    {
        return $this->belongsToMany(BudgetGLAccount::class, 't_BudgetLinesGLAccounts', 'BudgetLineID', 'BudgetGLAccountID')
            ->withTimestamps()
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy', 'DeletedOn'])
            ->wherePivot('DeletedOn', null); // Only fetch if DeletedOn is NULL
    }

    public function newGlAccounts()
    {
        return $this->belongsToMany(BudgetGLMaster::class, 't_BudgetLinesGLAccounts', 'BudgetLineID', 'BudgetGLAccountID')
            ->withTimestamps()
            ->withPivot(['CreatedBy', 'ModifiedBy', 'DeletedBy', 'DeletedOn'])
            ->wherePivot('DeletedOn', null); // Only fetch if DeletedOn is NULL
    }

    public function glAccountSubType()
    {
        return $this->belongsTo(BudgetGLAccountSubType::class, 'GLAccountSubTypeID', 'Id');
    }

    public function glSubType() //Implemented this due to the change in where we are pointing to the new Tables
    {
        return $this->belongsTo(BudgetGLSubType::class, 'GLAccountSubTypeID', 'Id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'Id');
    }

    public function glAccountType()
    {
        return $this->belongsTo(CodeDetail::class, 'GLAccountTypeID', 'Value');
    }

    public function productTypes()
    {
        return $this->belongsToMany(
            BudgetProduct::class,          // The related model
            't_BudgetLineProductTypes',        // The pivot table
            'BudgetLineId',                    // Foreign key on pivot pointing to this model
            'ProductTypeId',                   // Foreign key on pivot pointing to related model
            'Id',                              // Local key on this model
            'Id'                               // Local key on related model
        );
    }

    public function products()
    {
        return $this->belongsToMany(
            BudgetProduct::class,          // The related model
            't_BudgetLineProductTypes',        // The pivot table
            'BudgetLineId',                    // Foreign key on pivot pointing to this model
            'ProductTypeId',                   // Foreign key on pivot pointing to related model
            'Id',                              // Local key on this model
            'Id'                               // Local key on related model
        );
    }
}
