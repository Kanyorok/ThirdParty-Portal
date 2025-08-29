<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table = 't_Criterias';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = ['CriteriaName', 'Description', 'SectionID', 'IsActive'];

    protected $casts = ['IsActive' => 'boolean'];

    protected static function booted()
    {
        static::creating(fn($x) => $x->CreatedBy = optional(auth()->user())->id);
        static::updating(fn($x) => $x->ModifiedBy = optional(auth()->user())->id);
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
