<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyCategory extends Model
{
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
