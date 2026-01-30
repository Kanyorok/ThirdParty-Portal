<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LegalTemplateClause extends Pivot
{
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_legal_clause_template';
    protected $primaryKey = 'Id';
    public $incrementing = true;

    // We are not asking Eloquent to auto-manage timestamps here,
    // because the columns are custom (CreatedOn/ModifiedOn) and
    // we set them explicitly in helper methods on LegalTemplate.
    public $timestamps = false;

    protected $fillable = [
        'TemplateID',
        'ClauseID',
        'Position',
        'IsMandatory',
        'ClauseVersion',
        'TitleOverride',
        'ContentOverride',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'Position' => 'integer',
        'IsMandatory' => 'boolean',
    ];
}
