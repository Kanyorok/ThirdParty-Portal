<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyUnit extends Model
{
    //
    protected $table = 't_AddUnit';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'BlockID',
        'FloorID',
        'UnitCode',
        'UnitSize',
        'IsRentable',
        'CurrentStatus',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
}
