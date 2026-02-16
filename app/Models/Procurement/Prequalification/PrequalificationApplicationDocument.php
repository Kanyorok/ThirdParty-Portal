<?php

namespace App\Models\Procurement\Prequalification;

use App\Models\DMS\Document as DmsDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationApplicationDocument extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_SupplierPreqApplicationDocuments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'SupplierID', 'RoundID', 'CategoryID', 'SectionID', 'ApplicationID',
        'DocumentId', 'FileType', 'Description', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PrequalificationApplication::class, 'ApplicationID', 'ApplicationID');
    }

    public function dmsDocument(): BelongsTo
    {
        return $this->belongsTo(DmsDocument::class, 'DocumentId', 'DocumentId');
    }
}
