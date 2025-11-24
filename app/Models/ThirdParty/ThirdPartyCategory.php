<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParty\ThirdParties;

class ThirdPartyCategory extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ThirdPartiesCategories';
    protected $primaryKey = 'MappingID';

    protected $fillable = [
        'ThirdPartyId',
        'CategoryID',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    protected $casts = [
        'ThirdPartyId' => 'integer',
        'CategoryID' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'CategoryID', 'Id');
    }
}
