<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurrentJournal extends Model
{
    use UserActorTrait,SoftDeletes;
    protected $table = 't_FinanceRecurrentJournals';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'RecurrentJournalId';
    }

    protected $fillable = [
        'JournalEntryId',
        'StartDate',
        'CuttOffDate',
        'NextRunDate',
        'Frequency',
        'ReferenceName',
        'Description',
        'isVoucher',
        'SystemDescription',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];
}
