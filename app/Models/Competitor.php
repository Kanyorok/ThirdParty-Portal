<?php

namespace App\Models;

use App\Services\StaticListsService;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competitor extends Model
{
    use ImageTrait;
    use UserActorTrait;
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_Competitors';
    protected $primaryKey = 'CompetitorID';

    protected $fillable = [
                           "CompetitorName",
                           "LocationID",
                           "Logo",
                           "Email",
                           "Website",
                           "Phone",
                           "CoreBusiness",
                           "Clients",
                           "MarketShare",
                           "Processing",
                           'Notes',
                           'CreatedBy',
                           'ModifiedBy',
                           'DeletedBy',
                          ];

    protected $casts = ['Processing' => 'array'];

    public function strategies(): BelongsToMany
    {
        return $this->belongsToMany(CodeDetail::class, 't_CompetitorStrategy', 'CompetitorId', 'StrategyId', 'CompetitorID', 'ID')
            ->withPivot(['CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy'])->where('t_CRMCodeDetails.CodeID', StaticListsService::MarketingModes);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationID', 'ID');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(CRMImage::class, 'Logo', 'ImageID');
    }

    public function products(): HasMany
    {
        return $this->hasMany(CompetitorProduct::class, 'CompetitorId', 'CompetitorID');
    }


    public function items(): MorphMany
    {
         return $this->morphMany(DescriptionItem::class, 'item', 'Item', 'ItemID', 'CompetitorID');
    }

    protected function getImageName(): string
    {
        return $this->CompetitorName;
    }
}
