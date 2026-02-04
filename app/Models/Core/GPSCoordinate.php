<?php

namespace App\Models\Core;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GPSCoordinate extends Model
{
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    protected $table = 't_GPSCoordinates';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Source", "SourceID", "Latitude", "Longitude", "Extra",
        "CreatedBy", "CreatedOn", "ModifiedBy", "ModifiedOn",
    ];

    protected $casts = [
        'Latitude' => 'float',
        'Longitude' => 'float',
        'Extra' => 'array',
    ];

    public static function getPrimaryKey(): string
    {
        return 'GPSCoordinateID';
    }

    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Source", "SourceID", 'Id');
    }
}
