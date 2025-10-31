<?php

namespace App\Models\Settings;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class APICredential extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_SYSIntegrations';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Integration', 'Configuration',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'APICredential';
    }

    protected $hidden = ['Configuration'];

    protected $casts = [
        'Configuration' => 'object',//'array'
    ];
}
