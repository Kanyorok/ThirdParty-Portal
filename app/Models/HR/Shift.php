<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 't_HRShifts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'StartTime',
        'EndTime',
        'IsOvernight',
        'GraceMinutes',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];
}
