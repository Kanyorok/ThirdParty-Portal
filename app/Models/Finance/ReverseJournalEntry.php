<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReverseJournalEntry extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceReverseJournalEntries';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ReverseJournalEntryId';
    }

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'JournalEntryId',
        'OriginalJournalEntryID',
        'OriginalReferenceNumber',
        'ReversalDate',
        'Reason',
        'SystemDescription',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];
}
