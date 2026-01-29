<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalDispatch extends Model
{
    protected $table = 't_LegalDispatches';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalDocumentID', 'DispatchDate', 'DispatchedTo', 'DispatchMethod',
        'Status', 'Remarks', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'IsActive',
    ];

    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'LegalDocumentID');
    }
}
