<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyFloor extends Model
{
    //
    protected $table = 't_AddFloor';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'BlockID',
        'FloorLabel',
        'FloorNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
}
