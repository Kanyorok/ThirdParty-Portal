<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Sections';
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

    public function criteria()
    {
        return $this->hasMany(Criteria::class, 'SectionID', 'id');
    }
}
