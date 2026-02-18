<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryLegalRef extends Model
{
    protected $table = 't_HRDisciplinaryLegalRefs';
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
