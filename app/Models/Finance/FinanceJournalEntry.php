<?php

namespace App\Models\Finance;

use App\Models\Auth\User;
use App\Models\Core\Approval\Workflow;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Finance\ReverseJournalEntry;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceJournalEntry extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_FinanceJournalEntries';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'FinanceJournalEntryId';
    }

    protected $fillable = [
        'Date',
        'RefNo',
        'Type',
        'SourceModule',
        'ApprovalStatus',
        'ApprovalReason',
        'Status',
        'IdempotencyKey',
        'CurrencyID',
        'Description',
        'SystemDescription',
        'IsReversed',

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
        'IsReversed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->RefNo)) {
                $datePart = Carbon::now()->format('Ymd');
                $prefix = 'JV' . $datePart;

                $latestRef = DB::table('t_FinanceJournalEntries')
                    ->where('RefNo', 'like', "$prefix%")
                    ->orderByDesc('RefNo')
                    ->value('RefNo');

                $nextNumber = 1;
                if ($latestRef) {
                    $lastDigits = (int)substr($latestRef, -3);
                    $nextNumber = $lastDigits + 1;
                }

                $model->RefNo = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    //Relationships
    public function journalLines()
    {
        return $this->hasMany(FinanceJournalLines::class, 'JournalEntryId', 'Id');
    }

    public function recurringJournals()
    {
        return $this->hasMany(RecurrentJournal::class, 'JournalEntryId', 'Id');
    }

    public function reverseJournals()
    {
        return $this->hasMany(ReverseJournalEntry::class, 'JournalEntryId', 'Id');
    }

    // Relationship to find reversals where this journal is the original
    public function reversalsAsOriginal(){
        return $this->hasMany(ReverseJournalEntry::class,'OriginalJournalEntryID','Id');
    }

    public function sourceModule()
    {
        return $this->belongsTo(\App\Models\Core\Module::class, 'SourceModule', 'ModuleID');
    }

    public function modifiedBy():BelongsTo
    {
        return $this->belongsTo(User::class,'ModifiedBy','Id');
    }

    public function createdBy():BelongsTo
    {
        return $this->belongsTo(User::class,'CreatedBy','Id');
    }

    // Accessor to get source module name
    public function getSourceModuleNameAttribute()
    {
        return $this->sourceModule ? $this->sourceModule->Name : 'Finance';
    }

    // Get the reversal information if this journal was reversed
    public function getReversalInfoAttribute()
    {
        if ($this->Type === 'reversing') {
            return null; // This is a reversing journal, not reversed
        }

        $reversal = ReverseJournalEntry::where('OriginalJournalEntryID', $this->Id)->first();
        return $reversal;
    }

    // Get who reversed this journal
    public function getReversedByAttribute()
    {
        $reversal = $this->getReversalInfoAttribute();
        if ($reversal) {
            return User::find($reversal->CreatedBy);
        }
        return null;
    }

    /**
     * Workflows Relationships
     */
    public function workflows(): MorphMany
    {
        return $this->morphMany(
            Workflow::class,
            'source',
            'Source',      // Column name in t_Workflow table
            id: 'SourceID',    // ID column in t_Workflow table
            localKey: 'Id'           // Local key
        );
    }
    public function workflowHistory()
    {
        return $this->morphMany(
            WorkflowHistory::class,
            'source',
            'Source',
            'SourceID',
            'Id'
        );
    }


    public function workflowPending()
    {
        return $this->hasMany(WorkflowPending::class, 'SourceID', 'Id')
            ->where('Source', 'FinanceJournalEntryId')
            ->whereNull('DeletedOn');
    }


}
