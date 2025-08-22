<?php

namespace App\Models\Legal;

use App\Models\DMS\Document;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LegalTemplate extends Model
{
    use SoftDeletes, UserActorTrait,DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalTemplates';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Title',
        'DocumentType',
        'Description',
        'TemplateBody',
        'Tokens',
        'DocumentDMSID',
        'Version',
        'Status',
        'IsActive',
        'EffectiveFrom',
        'EffectiveTo',
        'Jurisdiction',
        'ApprovalStatus',
        'ApprovalReason',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalTemplatesId';
    }

    protected $casts = [
        'IsActive'      => 'boolean',
        'Tokens'        => 'array',
        'EffectiveFrom' => 'date',
        'EffectiveTo'   => 'date',
        'ApprovedOn'    => 'datetime',
    ];


    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    /** Relationship: clauses that belong to this template (ordered by Position) */
    public function clauses(): BelongsToMany
    {
        return $this->belongsToMany(
            LegalClause::class,
            't_legal_clause_template',
            'TemplateID',  // FK on pivot referencing this model
            'ClauseID'     // FK on pivot referencing the related model
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

    /* ---------- Helper methods for inserting/updating pivot rows ---------- */

    /**
     * Attach a single clause to this template with full control.
     *
     * @param  int|\App\Models\Legal\LegalClause $clause
     * @param  array{position?:int,is_mandatory?:bool,clause_version?:string,
     *               title_override?:string,content_override?:string,created_by?:int} $options
     */
    public function attachClause($clause, array $options = []): void
    {
        $clauseId = $clause instanceof LegalClause ? $clause->getKey() : (int) $clause;

        $position = $options['position'] ?? ($this->clauses()->max('t_legal_clause_template.Position') + 1) ?? 1;
        $userId   = $options['created_by'] ?? Auth::id();
        $now      = Carbon::now();

        $this->clauses()->syncWithoutDetaching([
            $clauseId => [
                'Position'       => (int) $position,
                'IsMandatory'    => (bool) ($options['is_mandatory'] ?? false),
                'ClauseVersion'  => $options['clause_version'] ?? null,
                'TitleOverride'  => $options['title_override'] ?? null,
                'ContentOverride'=> $options['content_override'] ?? null,
                'CreatedBy'      => $userId,
                'CreatedOn'      => $now,
                'ModifiedBy'     => $userId,
                'ModifiedOn'     => $now,
            ],
        ]);
    }

    /**
     * Sync the given clause IDs in the provided order (1..n).
     * Existing pivot rows will be updated to the new order;
     * rows not present will be removed.
     *
     * @param  array<int,int> $orderedClauseIds
     * @param  bool $clearOverrides If true, wipes overrides on reordering.
     */
    public function syncClausesOrdered(array $orderedClauseIds, bool $clearOverrides = false): void
    {
        $userId = Auth::id();
        $now = Carbon::now();

        $map = [];
        $pos = 1;
        foreach ($orderedClauseIds as $clauseId) {
            $map[(int)$clauseId] = [
                'Position'      => $pos++,
                'ModifiedBy'    => $userId,
                'ModifiedOn'    => $now,
            ];

            if ($clearOverrides) {
                $map[(int)$clauseId]['TitleOverride']   = null;
                $map[(int)$clauseId]['ContentOverride'] = null;
            }
        }

        $this->clauses()->sync($map);
    }

    /**
     * Insert all currently attached clauses (respecting Position) into a single text blob.
     * If overrides exist, they take precedence over master content.
     */
    public function mergedClausesText(): string
    {
        return $this->clauses
            ->sortBy('pivot.Position')
            ->map(function (LegalClause $c) {
                $title = $c->pivot->TitleOverride ?: $c->Title;
                $body  = $c->pivot->ContentOverride ?: $c->Content;
                return trim("{$title}\n\n{$body}");
            })
            ->implode("\n\n");
    }

    /* ---------- Scopes ---------- */

    public function scopeActive($q)
    {
        return $q->where('Status', 'ACTIVE')->where('IsActive', true);
    }

    public function scopeType($q, ?string $docType)
    {
        return $docType ? $q->where('DocumentType', $docType) : $q;
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($sub) use ($term) {
            $sub->where('Title', 'like', "%{$term}%")
                ->orWhere('DocumentType', 'like', "%{$term}%")
                ->orWhere('Description', 'like', "%{$term}%");
        });
    }
}
