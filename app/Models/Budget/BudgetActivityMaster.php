<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetActivityMaster extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetActivityMaster';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'BudgetLineID',
        //'ActivityCode',
        'ActivityName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 't_BudgetActivityMasterId';
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID', 'Id');
    }

}
