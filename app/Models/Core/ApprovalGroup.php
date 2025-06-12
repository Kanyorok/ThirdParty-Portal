<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ApprovalGroup extends Model
{
    //
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';

    protected $table = 't_ApprovalGroups';


    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocType',
        'ApprovalType',
        'Permission',
        'CreatedBy',
        'ModifiedBy'

    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];
}
