<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReverseJournalEntry extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_FinanceReverseJournalEntries';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ReverseJournalEntryId';
    }

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(FinanceJournalEntry::class, 'JournalEntryId', 'Id');
    }
}
