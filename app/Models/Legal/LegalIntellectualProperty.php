<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalIntellectualProperty extends Model
{
    protected $table = 't_LegalIntellectualProperties';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Title',
        'Type', // e.g. Trademark, Patent, Copyright
        'RegistrationNumber',
        'FilingDate',
        'ExpiryDate',
        'Status', // e.g. Active, Expired, Disputed, Pending Renewal
        'Owner',
        'Jurisdiction',
        'DMSDocID', // DMS integration
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
        'IsActive'
    ];
}
