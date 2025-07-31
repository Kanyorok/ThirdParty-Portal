<?php

namespace App\Models\Finance;

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
        'CDNumber',
        'NoteType',
        'InvoiceRefNo',
        'NoteDate',
        'NoteAmount',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey() : string 
    {
        return 'FinanceCDNotesId'; 
    }

    public function invoice()
    {
        return $this->belongsTo(FinanceInvoiceEntry::class, 'InvoiceRefNo', 'Id');
    }
}
