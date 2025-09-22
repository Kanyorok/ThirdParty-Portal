<?php

namespace App\Models\Finance;

use App\Models\Core\Module;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransactionTypes extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_FinanceTransactionTypes';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'TransactionTypesId';
    }

    // Relationships

    public function module()
    {
        return $this->belongsTo(Module::class, 'ModuleID', 'ModuleID');
    }
}
