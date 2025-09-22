<?php

namespace App\Models\Finance;

use App\Models\Auth\User;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceCDNotes extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $primaryKey = 'Id';
    protected $table = 't_FinanceCDNotes';
    protected $fillable = [
        'NoteType',
        'InvoiceRefNo',
        'NoteDate',
        'NoteAmount',
        'ApprovalStatus',
        'ApprovalReason',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'FinanceCDNotesId';
    }

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'InvoiceRefNo', 'Id')
            ->where('ApprovalStatus', 'posted');
    }

    public function invoiceDebit()
    {
        return $this->belongsTo(FinanceInvoice::class, 'InvoiceRefNo', 'Id')
            ->where('ApprovalStatus', 'posted');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    protected static function booted()
    {
        static::creating(function ($note) {
            $year = now()->year;
            $lastId = self::max('Id') + 1;
            $note->CDNumber = $year . '-' . str_pad($lastId, 6, '0', STR_PAD_LEFT);
        });
    }
}
