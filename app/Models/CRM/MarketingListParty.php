<?php

namespace App\Models\CRM;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingListParty extends Pivot
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_MarketingListParties';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'MarketingListId',
                           'Party',
                           'PartyID',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                           'DeletedOn',
                          ];


    //Client, Leads
    public function party(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(MarketingList::class, 'MarketingListId', 'MarketingListID');
    }
}
