<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class APICredential extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_SYSIntegrations';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Integration', 'Configuration',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'APICredential';
    }

    protected $hidden = [
        'Configuration'
    ];

    protected $casts = [
        'Configuration' => 'object'//'array'
    ];
}
