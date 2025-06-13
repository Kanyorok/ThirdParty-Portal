<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyLeaseTermination extends Model
{
    //
    protected $table = 't_TerminateLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseID',
        'TerminationDate',
        'TerminationReason',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

}
