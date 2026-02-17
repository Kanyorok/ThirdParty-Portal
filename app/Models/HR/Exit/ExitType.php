<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;

class ExitType extends Model
{
    protected $table = 't_HRExitTypes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'IsEmployerInitiated',
        'RequiresCase',
        'RequiresHearing',
        'IsRedundancy',
        'IsSummaryDismissal',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsEmployerInitiated' => 'boolean',
        'RequiresCase' => 'boolean',
        'RequiresHearing' => 'boolean',
        'IsRedundancy' => 'boolean',
        'IsSummaryDismissal' => 'boolean',
        'IsActive' => 'boolean',
    ];
}
