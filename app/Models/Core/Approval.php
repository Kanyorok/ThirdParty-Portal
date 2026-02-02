<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';

    protected $primaryKey = 'Id';

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
