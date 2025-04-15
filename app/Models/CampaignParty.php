<?php

namespace App\Models;

use App\Enums\EmailStatusEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CampaignParty extends Model
{
    use UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    protected $connection = 'sqlsrv';
    protected $table = 't_CampaignParties';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
                           'CampaignId',
                           'Party',
                           'PartyID',
                           'Status',
                           "Channel",
                           "ChannelID",
                           'CreatedBy',
                           'ModifiedBy',
                          ];
    protected $casts = [
                        'Status' => EmailStatusEnum::class,
                       ];

    public static function getPrimaryKey(): string
    {
        return 'CampaignParty';
    }

    //Client, Leads
    public function party(): MorphTo
    {
        return /*($this->Party === DebtProduct::getPrimaryKey())
            ? $this->morphTo(__FUNCTION__, "Party", "PartyID",'AccountID')->latest('processDate')
            :*/ $this->morphTo(__FUNCTION__, "Party", "PartyID");
    }

    /**
     * Email and SMS
     * @return MorphTo
     */
    public function channel(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Channel", "ChannelID");
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'CampaignId', 'Id');
    }
}
