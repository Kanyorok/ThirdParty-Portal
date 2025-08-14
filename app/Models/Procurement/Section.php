<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Procurement\Criteria;

class Section extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Sections';
    protected $primaryKey = 'id';

    protected $fillable = [
        'SectionName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public static function getPrimaryKey(): string
    {
        return (new static())->primaryKey;
    }

    public function getRouteKeyName(): string
    {
        return $this->primaryKey;
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class, 'SectionID', 'id');
    }
}
