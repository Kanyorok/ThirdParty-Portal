<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeDetail extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_CRMCodeDetails';
    protected $primaryKey = 'ID';

    protected $fillable = [
                           'CodeID',
                           'Description',
                           'DisplayOrder',
                           'IsActive',
                           'CreatedOn',
                           'CreatedBy',
                           'ModifiedOn',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = ['DisplayOrder' => 'integer'];
}
