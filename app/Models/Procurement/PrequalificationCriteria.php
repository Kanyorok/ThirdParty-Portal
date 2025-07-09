<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationCriteria extends Model
{
    use SoftDeletes, UserActorTrait;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_PrequalificationRoundCriteria';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'RoundId',
        'SectionId',
        'CriteriaId',
        'Included',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'prequalificationcriteriaId';
    }
}
