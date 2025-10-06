<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChequeLeaf extends Model
{
    use SoftDeletes;

    protected $table = 't_ChequeLeaves';
    protected $primaryKey = 'LeafID';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ChequeBookID','LeafNumber','ChequeNumber','Status',
        'ChequeID','ReservedOn','UsedOn','ClearedOn','CancelledOn','Notes',
        'CreatedBy','ModifiedBy','DeletedBy',
    ];

    public static function getPrimaryKey(): string { return 'LeafID'; }

    public function book()   { return $this->belongsTo(ChequeBook::class, 'ChequeBookID', 'ChequeBookID'); }
    public function cheque() { return $this->belongsTo(Cheque::class, 'ChequeID', 'ChequeID'); }
}
