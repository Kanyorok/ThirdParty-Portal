<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LegalClause extends Model
{
    use SoftDeletes, UserActorTrait;

    // Custom timestamp columns (match your DB)
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalClauses';
    protected $primaryKey = 'Id';

    // Mass-assignable (kept your original + new optional governance fields)
    protected $fillable = [
        'Title',
        'ClauseType',
        'Content',
        'Version',
        'IsStandard',         // stored as 'Yes'/'No' to match your controller
        'ClauseDMSDocID',
        'Status',
        'EffectiveFrom',
        'EffectiveTo',
        'Jurisdiction',
        'Tags',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    // Casts for convenience
    protected $casts = [
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'Tags' => 'array',
    ];

    // (Fix the incorrect returned name)
    public static function getPrimaryKey(): string
    {
        return 'LegalClausesId';
    }

    /** Relationship: templates that include this clause */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(
            LegalTemplate::class,
            't_legal_clause_template',
            'ClauseID',    // FK on pivot referencing this model
            'TemplateID'   // FK on pivot referencing the related model
        )
            ->using(LegalTemplateClause::class)
            ->withPivot([
                'Id',
                'Position',
                'IsMandatory',
                'ClauseVersion',
                'TitleOverride',
                'ContentOverride',
                'CreatedBy',
                'CreatedOn',
                'ModifiedBy',
                'ModifiedOn',
            ])
            ->orderBy('t_legal_clause_template.Position');
    }

    /** Convenience accessor: boolean view of IsStandard (stored as 'Yes'/'No') */
    public function getIsStandardBoolAttribute(): bool
    {
        $v = $this->attributes['IsStandard'] ?? 'No';
        return strcasecmp((string)$v, 'Yes') === 0 || $v === 1 || $v === true;
    }

    /** Mutator: allow boolean assignment, store as 'Yes'/'No' */
    public function setIsStandardAttribute($value): void
    {
        // Accepts 'Yes'/'No', true/false, '1'/'0'
        if (is_bool($value)) {
            $this->attributes['IsStandard'] = $value ? 'Yes' : 'No';
        } elseif (in_array($value, [1, '1', 'true', 'TRUE', 'yes', 'YES'], true)) {
            $this->attributes['IsStandard'] = 'Yes';
        } elseif (in_array($value, [0, '0', 'false', 'FALSE', 'no', 'NO'], true)) {
            $this->attributes['IsStandard'] = 'No';
        } else {
            $this->attributes['IsStandard'] = (string)$value;
        }
    }

    /* ---------- Scopes ---------- */

    public function scopeActive($q)
    {
        return $q->where('Status', 'ACTIVE');
    }

    public function scopeType($q, ?string $type)
    {
        return $type ? $q->where('ClauseType', $type) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($sub) use ($term) {
            $sub->where('Title', 'like', "%{$term}%")
                ->orWhere('ClauseType', 'like', "%{$term}%")
                ->orWhere('Content', 'like', "%{$term}%");
        });
    }
}
