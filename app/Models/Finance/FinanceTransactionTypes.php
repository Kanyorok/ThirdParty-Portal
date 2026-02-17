<?php

namespace App\Models\Finance;

use App\Models\Core\Module;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransactionTypes extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_FinanceTransactionTypes';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
