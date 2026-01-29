<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ApprovalGroup extends Model
{
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';

    protected $table = 't_ApprovalGroups';


    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocType',
        'ApprovalType',
        'Permission',
        'CreatedBy',
        'ModifiedBy',

    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];
}
