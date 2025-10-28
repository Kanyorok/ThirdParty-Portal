<?php

namespace App\Models\ThirdParies;

use App\Models\Core\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\CRM\CompetitorProduct;
use App\Models\CRM\DescriptionItem;
use App\Models\DMS\Image;
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
    use SoftDeletes, UserActorTrait, ImageTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_Competitors';
    protected $primaryKey = 'CompetitorID';

    public static function getPrimaryKey(): string
    {
        return 'CompetitorID';
    }

    protected $fillable = [
        "CompetitorName", "LocationID", 'CountryId', "Logo", "Email", "Website", "Phone", "CoreBusiness", "Clients", "MarketShare", "Processing", 'Notes',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = ['Processing' => 'array'];

    public function strategies(): BelongsToMany
    {
        return $this->belongsToMany(CodeDetail::class, 't_CompetitorStrategy', 'CompetitorId', 'StrategyId', 'CompetitorID', 'ID')
            ->withPivot(['CreatedOn', 'CreatedBy', 'ModifiedOn', 'ModifiedBy'])->where('t_CodeDetails.CodeID', StaticListsService::MarketingModes);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'LocationID', 'ID');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'Logo', 'ImageID');
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
