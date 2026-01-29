<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurrentJournal extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FinanceRecurrentJournals';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
