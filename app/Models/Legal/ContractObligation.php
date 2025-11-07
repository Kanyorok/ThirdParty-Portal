<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ContractObligation extends Model
{
    protected $table = 't_ContractObligations';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalDocumentID', 'ObligationTitle', 'ObligationType', 'DueDate',
        'Status', 'AssignedTo', 'Remarks', 'CreatedBy', 'CreatedOn',
        'ModifiedBy', 'ModifiedOn', 'IsActive'
    ];

    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'LegalDocumentID');
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'AssignedTo');
    }
}
