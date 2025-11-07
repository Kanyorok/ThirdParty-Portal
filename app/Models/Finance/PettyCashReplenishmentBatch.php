<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashReplenishmentBatch extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_PettyCashReplenishmentBatches';
    protected $primaryKey = 'BatchID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FloatID', 'BankAccountID', 'BatchDate', 'TotalAmount', 'Status', 'CashbookID',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'BatchID';
    }

    public function float()
    {
        return $this->belongsTo(PettyCashFloat::class, 'FloatID', 'FloatID');
    }
}
