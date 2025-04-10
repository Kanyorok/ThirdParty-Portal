<?php

namespace App\Models;

use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingList extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_MarketingLists';
    protected $primaryKey = 'MarketingListID';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug', 'Label', 'Extra', 'Type', 'LastContacted', 'Source', 'Visibility', 'Processing',
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'CreatedBy' => 'integer',
        'Type' => MarketingListEnum::class,
        'Visibility' => VisibilityEnum::class,
        'Extra' => 'object',
        'LastContacted' => 'datetime',
        'Processing' => 'array'
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'MarketingListId', 'MarketingListID')->withTrashed();
    }

    public function parties(): HasMany
    {
        return $this->hasMany(MarketingListParty::class, 'MarketingListId', 'MarketingListID');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(MarketingListFilter::class, 'MarketingListId', 'MarketingListID');
    }

}
