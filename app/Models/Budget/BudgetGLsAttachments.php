<?php

namespace App\Models\Budget;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetGLsAttachments extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetGLsAttachmentsID';
    }

    protected $table = 't_BudgetGLsAttachments';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'BudgetID',
        'GLID',
        'AccountID',
        'Description',
        'GLAccountTypeID',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function glMaster()
    {
        return $this->belongsTo(BudgetGLMaster::class, 'GLID', 'BudgetGLID');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
}
