<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Criterias';
    protected $primaryKey = 'id';

    protected $fillable = [
        'CriteriaName',
        'Description',
        'SectionID',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'SectionID', 'id');
    }

    public static function getPrimaryKey(): string
    {
        return (new static())->primaryKey;
    }
}
