<?php

namespace App\Models\Procurement\Prequalification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DMS\Document as DmsDocument;

class PrequalificationApplicationDocument extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
