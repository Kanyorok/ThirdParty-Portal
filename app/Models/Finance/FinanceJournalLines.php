<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceJournalLines extends Model
{
    use UserActorTrait,SoftDeletes;


    protected $table = 't_FinanceJournalLines';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'FinanceJournalLinesId';
    }

    protected $fillable = [
        'JournalEntryId',
        'GLAccountID',
        'BranchID',
        'DepartmentID',
        'Debit',
        'Credit',
        'Amount',
        'IsDebit',
        'Narration',
        'SystemDescription',

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

    //Relationships
    public function journalEntry()
    {
        return $this->belongsTo(FinanceJournalEntry::class, 'JournalEntryId', 'Id');
    }

    public function glAccount()
    {
        return $this->belongsTo(FinanceGLAccounts::class, 'GLAccountID')
            ->select('Id', 'GLAccountTypeID', 'GLTypeGroupID', 'GLSubAccountTypeID', 'GLName', 'GLTypeGroupID', 'GLCode');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'BranchID');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'DepartmentID');
    }
}
