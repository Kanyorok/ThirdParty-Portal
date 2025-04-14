<?php

namespace App\Models;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingListFilter extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    //todo v2 add Groups to be able to Group Queries.

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_MarketingListsFilters';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'MarketingListId',
                           'FilterId',
                           'FilterValue',
                           'FilterValues',
                           'After',
                           'DisplayOrder',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = [
                        'FilterValues' => 'array',
                        'DisplayOrder' => 'integer',
                       ];

    public function filter(): BelongsTo
    {
        return $this->belongsTo(SysFilter::class, 'FilterId', 'Id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(MarketingList::class, 'MarketingListId', 'MarketingListID');
    }
}
