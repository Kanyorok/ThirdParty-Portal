<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RFQSettingSection extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_RFQSettingSections';

    protected $fillable = [
        'SectionName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    protected $primaryKey = 'id';

    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function criteriaSettings()
    {
        return $this->hasMany(RFQSettingCriteria::class, 'SectionID', 'id');
    }
}
