<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class RFQClarification extends Model
{
    protected $table = 't_RFQClarifications';
    protected $primaryKey = 'Id';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'RFQId', 'SupplierId', 'RFQLineId', 'Question', 'Answer', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];
}
