<?php

namespace App\Models\Procurement;

use App\Enums\Procurement\PrequalificationPeriodEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationPeriod extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $table = 't_PrequalificationPeriod';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Title',
        'Description',
        'StartDate',
        'EndDate',
        'MaxVendors',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'prequalificationperiodId';
    }

    protected $casts = [
        'Status' => PrequalificationPeriodEnum::class,
    ];
}
