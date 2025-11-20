<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChequeBook extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_ChequeBooks';
    protected $primaryKey = 'ChequeBookID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'BankAccountID', 'BookName', 'Prefix', 'Suffix',
        'StartNumber', 'EndNumber', 'NextLeafNumber',
        'LeavesTotal', 'LeavesIssued', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'StartNumber' => 'integer',
        'EndNumber' => 'integer',
        'NextLeafNumber' => 'integer',
        'LeavesTotal' => 'integer',
        'LeavesIssued' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ChequeBookID';
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'BankAccountID', 'AccountID');
    }
}
