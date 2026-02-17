<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeContact extends Model
{
    protected $table = 't_HREmployeeContacts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'Name',
        'Relation',
        'Phone',
        'Email',
        'IsPrimary',
        'IsNextOfKin',
        'IsEmergency',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsPrimary' => 'boolean',
        'IsNextOfKin' => 'boolean',
        'IsEmergency' => 'boolean',
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
