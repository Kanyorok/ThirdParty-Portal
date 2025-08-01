<?php

namespace App\Models\Finance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
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
        'ApprovalStatus',
        'Description',

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
                    $lastDigits = (int) substr($latestRef, -3);
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

}
