<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_Sections';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'SectionName',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        // Ignore these; Meant for a pivot table; bad casing
        //   'sectionable_id',
        // 'sectionable_type',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($x) {
            $x->CreatedBy = auth()->id() ?? 1;
        });
        static::updating(function ($x) {
            $x->ModifiedBy = auth()->id() ?? 1;
        });
    }

    public static function getPrimaryKey(): string
    {
        return (new static())->primaryKey;
    }

    public function getRouteKeyName(): string
    {
        return $this->primaryKey;
    }

    public function rfqSections()
    {
        return $this->hasMany(RFQSection::class, 'SectionID', 'id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class, 'SectionID', 'Id')->where('IsActive', true);
    }

    public function sectionable()
    {
        return $this->morphTo();
    }

    // Section::active()->get();
    public function scopeIsActive($q)
    {
        return $q->where('IsActive', 1);
    }
}
