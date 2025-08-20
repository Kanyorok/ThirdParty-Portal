<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalCounsel extends Model
{
    protected $table = 't_LegalCounsels';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalCaseID', 'CounselName', 'LawFirm',
        'ContactEmail', 'ContactPhone', 'Role',
        'Remarks', 'CreatedBy', 'CreatedOn',
        'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn', 'IsActive'
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID', 'ID');
    }
}
