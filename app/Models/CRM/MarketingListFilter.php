<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingListFilter extends Model
{ //todo v2 add Groups to be able to Group Queries.
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingListsFilters';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'MarketingListsFiltersId';
    }
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
