<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $table = 't_Criterias';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = ['CriteriaName', 'Description', 'SectionID', 'IsActive'];

    protected $casts = ['IsActive' => 'boolean'];

    protected static function booted()
    {
        static::creating(fn ($x) => $x->CreatedBy = optional(auth()->user())->id);
        static::updating(fn ($x) => $x->ModifiedBy = optional(auth()->user())->id);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'SectionID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return (new static())->primaryKey;
    }
}
