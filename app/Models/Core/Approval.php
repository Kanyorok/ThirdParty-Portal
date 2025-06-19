<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';

    protected $primaryKey = 'Id';
    //
    protected $table = 't_Approvals';

    protected $fillable = [
        'DocType',
        'DocumentId',
        'UserId',
        'CreatedBy',
        'ModifiedBy',

    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];
}
