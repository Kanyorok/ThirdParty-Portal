<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;

class ExitLegalRef extends Model
{
    protected $table = 't_HRExitLegalRefs';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Section',
        'Title',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];
}
